<?php

namespace App\Http\Controllers;

use App\Models\Marchand;
use App\Models\Commune;
use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MarchandController extends Controller
{
    /**
     * Vérifier l'accès à un marchand
     */
    private function checkMarchandAccess($marchand, $user)
    {
        // Permettre aux super admins d'accéder à tous les marchands
        if($user->user_type === 'super_admin') {
            return true;
        }

        $userEntrepriseId = (int) $user->entreprise_id;
        $marchandEntrepriseId = (int) $marchand->entreprise_id;

        return $marchandEntrepriseId === $userEntrepriseId;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['title'] = 'Liste des Marchands';
        $data['menu'] = 'marchands';

        $data['user'] = Auth::user();
        if(empty($data['user'])){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Debug: Vérifier l'entreprise_id de l'utilisateur
        \Log::info('MarchandController - User entreprise_id: ' . ($data['user']->entreprise_id ?? 'NULL'));

        // Récupérer les marchands avec leurs communes en fonction de l'entreprise
        $query = Marchand::with('commune');

        if ($data['user']->entreprise_id) {
            $query->where('entreprise_id', $data['user']->entreprise_id);
        } else {
            // Si l'utilisateur n'a pas d'entreprise_id, récupérer tous les marchands
            \Log::info('MarchandController - User sans entreprise_id, récupération de tous les marchands');
        }

        $data['marchands'] = $query->orderBy('created_at', 'desc')->paginate(15);

        // Debug: Vérifier le nombre de marchands trouvés
        \Log::info('MarchandController - Nombre de marchands trouvés: ' . $data['marchands']->count());

        return view('marchand.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Ajouter un Marchand';
        $data['menu'] = 'create-marchand';

        $data['user'] = Auth::user();
        if(empty($data['user'])){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $data['communes'] = Commune::orderBy('libelle')->get();

        return view('marchand.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if(empty($user)){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'mobile' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10|max:15',
            'email' => 'nullable|email|max:255|unique:marchands,email',
            'adresse' => 'nullable|string|max:500',
            'commune_id' => 'required|exists:communes,id',
            'status' => 'required|in:active,inactive'
        ], [
            'first_name.regex' => 'Le prénom ne peut contenir que des lettres et espaces.',
            'last_name.regex' => 'Le nom ne peut contenir que des lettres et espaces.',
            'mobile.regex' => 'Le format du numéro de téléphone est invalide.',
            'commune_id.required' => 'Veuillez sélectionner une commune.',
            'commune_id.exists' => 'La commune sélectionnée n\'existe pas.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'status.in' => 'Le statut doit être actif ou inactif.'
        ]);

        try {
            DB::beginTransaction();

            $marchand = Marchand::create([
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'mobile' => trim($request->mobile),
                'email' => $request->email ? strtolower(trim($request->email)) : null,
                'adresse' => $request->adresse ? trim($request->adresse) : null,
                'commune_id' => $request->commune_id,
                'status' => $request->status,
                'created_by' => $user->id,
                'entreprise_id' => $user->entreprise_id
            ]);

            DB::commit();

            Log::info('Marchand créé avec succès', [
                'marchand_id' => $marchand->id,
                'nom' => $marchand->full_name,
                'created_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('marchands.index')
                ->with('success', 'Le marchand a été créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la création du marchand', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la création du marchand. Veuillez réessayer.'])
                ->withInput($request->except('password'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Marchand $marchand)
    {
        $data['title'] = 'Détails du Marchand';
        $data['menu'] = 'marchands';

        $data['user'] = Auth::user();
        if(empty($data['user'])){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Debug: Vérifier les valeurs
        \Log::info('MarchandController::show - User entreprise_id: ' . ($data['user']->entreprise_id ?? 'NULL'));
        \Log::info('MarchandController::show - User user_type: ' . ($data['user']->user_type ?? 'NULL'));
        \Log::info('MarchandController::show - Marchand entreprise_id: ' . ($marchand->entreprise_id ?? 'NULL'));
        \Log::info('MarchandController::show - Marchand ID: ' . $marchand->id);

        // Vérifier l'accès au marchand
        if(!$this->checkMarchandAccess($marchand, $data['user'])){
            \Log::info('MarchandController::show - Accès refusé');
            return redirect()->route('marchands.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce marchand.']);
        }

        // Récupérer les informations de base du marchand
        $data['marchand'] = $marchand->load('commune');
        // dd($data['marchand']);

        // Récupérer les boutiques qui appartiennent à ce marchand
        $boutiques = Boutique::where('marchand_id', $marchand->id)->get();
        $data['boutiques'] = $boutiques;
        $data['boutiques_count'] = $boutiques->count();

        // Récupérer les colis de ce marchand via commune_zone
        $colis = \App\Models\Colis::whereHas('commune_zone', function($query) use ($marchand) {
            $query->where('marchand_id', $marchand->id);
        })
        ->where('entreprise_id', $marchand->entreprise_id)
        ->orderBy('created_at', 'desc')
        ->get();

        $data['colis'] = $colis;
        $data['colis_count'] = $colis->count();

        return view('marchand.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marchand $marchand)
    {
        $data['title'] = 'Modifier le Marchand';
        $data['menu'] = 'marchands';

        $data['user'] = Auth::user();
        if(empty($data['user'])){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Vérifier que le marchand appartient à l'utilisateur connecté
        if($marchand->entreprise_id != $data['user']->entreprise_id){
            return redirect()->route('marchands.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce marchand.']);
        }

        $data['marchand'] = $marchand;
        $data['communes'] = Commune::orderBy('libelle')->get();

        return view('marchand.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Marchand $marchand)
    {
        $user = Auth::user();
        if(empty($user)){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Vérifier que le marchand appartient à l'utilisateur connecté
        if($marchand->entreprise_id != $user->entreprise_id){
            return redirect()->route('marchands.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce marchand.']);
        }

        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'mobile' => 'required|string|regex:/^[0-9+\-\s()]+$/|min:10|max:15',
            'email' => 'nullable|email|max:255|unique:marchands,email,' . $marchand->id,
            'adresse' => 'nullable|string|max:500',
            'commune_id' => 'required|exists:communes,id',
            'status' => 'required|in:active,inactive'
        ], [
            'first_name.regex' => 'Le prénom ne peut contenir que des lettres et espaces.',
            'last_name.regex' => 'Le nom ne peut contenir que des lettres et espaces.',
            'mobile.regex' => 'Le format du numéro de téléphone est invalide.',
            'commune_id.required' => 'Veuillez sélectionner une commune.',
            'commune_id.exists' => 'La commune sélectionnée n\'existe pas.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'status.in' => 'Le statut doit être actif ou inactif.'
        ]);

        try {
            DB::beginTransaction();

            $marchand->update([
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'mobile' => trim($request->mobile),
                'email' => $request->email ? strtolower(trim($request->email)) : null,
                'adresse' => $request->adresse ? trim($request->adresse) : null,
                'commune_id' => $request->commune_id,
                'status' => $request->status
            ]);

            DB::commit();

            Log::info('Marchand modifié avec succès', [
                'marchand_id' => $marchand->id,
                'nom' => $marchand->full_name,
                'updated_by' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->route('marchands.index')
                ->with('success', 'Le marchand a été modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la modification du marchand', [
                'marchand_id' => $marchand->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la modification du marchand. Veuillez réessayer.'])
                ->withInput($request->except('password'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marchand $marchand)
    {
        $user = Auth::user();
        if(empty($user)){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Vérifier que le marchand appartient à l'utilisateur connecté
        if($marchand->entreprise_id != $user->entreprise_id){
            return redirect()->route('marchands.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce marchand.']);
        }

        try {
            DB::beginTransaction();

            $marchandName = $marchand->full_name;
            $marchandId = $marchand->id;

            // Vérifier s'il y a des colis associés
            if($marchand->colis()->count() > 0){
                return redirect()->back()
                    ->withErrors(['error' => 'Impossible de supprimer ce marchand car il a des colis associés.']);
            }

            // Vérifier s'il y a des boutiques associées
            if($marchand->boutiques()->count() > 0){
                return redirect()->back()
                    ->withErrors(['error' => 'Impossible de supprimer ce marchand car il a des boutiques associées.']);
            }

            $marchand->delete();

            DB::commit();

            Log::info('Marchand supprimé avec succès', [
                'marchand_id' => $marchandId,
                'nom' => $marchandName,
                'deleted_by' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->route('marchands.index')
                ->with('success', 'Le marchand a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression du marchand', [
                'marchand_id' => $marchand->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la suppression du marchand. Veuillez réessayer.']);
        }
    }

    /**
     * Rechercher des marchands
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        if(empty($user)){
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $query = $request->get('q');

        if(empty($query)){
            return response()->json([]);
        }

        $marchands = Marchand::with('commune')
            ->where('entreprise_id', $user->entreprise_id)
            ->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($marchands);
    }

    /**
     * Changer le statut d'un marchand
     */
    public function toggleStatus(Marchand $marchand)
    {
        $user = Auth::user();
        if(empty($user)){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Vérifier que le marchand appartient à l'utilisateur connecté
        if($marchand->entreprise_id != $user->entreprise_id){
            return redirect()->route('marchands.index')
                ->withErrors(['error' => 'Vous n\'avez pas accès à ce marchand.']);
        }

        try {
            $newStatus = $marchand->status === 'active' ? 'inactive' : 'active';
            $marchand->update(['status' => $newStatus]);

            Log::info('Statut du marchand modifié', [
                'marchand_id' => $marchand->id,
                'nom' => $marchand->full_name,
                'nouveau_statut' => $newStatus,
                'updated_by' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->with('success', 'Le statut du marchand a été modifié avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut', [
                'marchand_id' => $marchand->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'ip' => request()->ip()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors du changement de statut.']);
        }
    }
}
