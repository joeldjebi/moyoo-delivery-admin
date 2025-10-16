<?php

namespace App\Http\Controllers;

use App\Models\Livreur;
use App\Models\Engin;
use App\Models\Zone;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LivreurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Gestion des Livreurs';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreurs'] = Livreur::with(['engin.typeEngin', 'zoneActivite', 'communes'])
                ->orderBy('first_name')
                ->paginate(15);

            return view('livreurs.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la liste des livreurs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de la liste des livreurs.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Ajouter un Livreur';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['engins'] = Engin::where('status', 'actif')
                ->with('typeEngin')
                ->orderBy('libelle')
                ->get();

            $data['communes'] = Commune::orderBy('libelle')
                ->get();

            return view('livreurs.create', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire de création: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20|unique:livreurs,mobile',
                'email' => 'nullable|email|max:255|unique:livreurs,email',
                'engin_id' => 'required|exists:engins,id',
                'zone_activite_id' => 'nullable|exists:communes,id',
                'communes' => 'nullable|array',
                'communes.*' => 'exists:communes,id',
                'permis' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:500',
                'password' => 'required|string|min:8|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            // Gérer l'upload de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('livreurs', 'public');
            }

            $livreur = Livreur::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'engin_id' => $request->engin_id,
                'zone_activite_id' => $request->zone_activite_id,
                'permis' => $request->permis,
                'adresse' => $request->adresse,
                'password' => Hash::make($request->password),
                'photo' => $photoPath,
                'status' => 'actif',
                'created_by' => $user->id
            ]);

            // Attacher les communes sélectionnées
            if ($request->has('communes') && is_array($request->communes)) {
                $livreur->communes()->attach($request->communes);
            }

            DB::commit();

            Log::info('Livreur créé avec succès', [
                'livreur_id' => $livreur->id,
                'nom' => $livreur->first_name . ' ' . $livreur->last_name,
                'user_id' => $user->id
            ]);

            return redirect()->route('livreurs.index')
                ->with('success', 'Livreur créé avec succès !');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du livreur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du livreur.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Détails du Livreur';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur->load(['engin.typeEngin', 'zoneActivite', 'communes']);

            return view('livreurs.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des détails du livreur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des détails du livreur.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Modifier le Livreur';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur->load('communes');
            $data['engins'] = Engin::where('status', 'actif')
                ->with('typeEngin')
                ->orderBy('libelle')
                ->get();

            $data['communes'] = Commune::orderBy('libelle')
                ->get();

            return view('livreurs.edit', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Livreur $livreur)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20|unique:livreurs,mobile,' . $livreur->id,
                'email' => 'nullable|email|max:255|unique:livreurs,email,' . $livreur->id,
                'engin_id' => 'required|exists:engins,id',
                'communes' => 'nullable|array',
                'communes.*' => 'exists:communes,id',
                'permis' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:500',
                'status' => 'required|in:actif,inactif',
                'password' => 'nullable|string|min:8|confirmed',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'engin_id' => $request->engin_id,
                'permis' => $request->permis,
                'adresse' => $request->adresse,
                'status' => $request->status,
                'updated_by' => $user->id
            ];

            // Mettre à jour le mot de passe si fourni
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Gérer l'upload de la nouvelle photo
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $updateData['photo'] = $photo->store('livreurs', 'public');
            }

            $livreur->update($updateData);

            // Mettre à jour les communes
            if ($request->has('communes')) {
                $livreur->communes()->sync($request->communes ?? []);
            }

            DB::commit();

            Log::info('Livreur mis à jour avec succès', [
                'livreur_id' => $livreur->id,
                'nom' => $livreur->first_name . ' ' . $livreur->last_name,
                'user_id' => $user->id
            ]);

            return redirect()->route('livreurs.index')
                ->with('success', 'Livreur mis à jour avec succès !');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du livreur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du livreur.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livreur $livreur)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier si le livreur a des colis assignés
            $colisCount = $livreur->colis()->count();
            if ($colisCount > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer ce livreur car il a ' . $colisCount . ' colis assigné(s).');
            }

            DB::beginTransaction();

            $livreur->update([
                'deleted_by' => $user->id
            ]);
            $livreur->delete();

            DB::commit();

            Log::info('Livreur supprimé avec succès', [
                'livreur_id' => $livreur->id,
                'nom' => $livreur->first_name . ' ' . $livreur->last_name,
                'user_id' => $user->id
            ]);

            return redirect()->route('livreurs.index')
                ->with('success', 'Livreur supprimé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du livreur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du livreur.');
        }
    }

    /**
     * Afficher l'historique des colis d'un livreur
     */
    public function colisHistory(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Colis du Livreur - ' . $livreur->first_name . ' ' . $livreur->last_name;

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur;
            $data['colis'] = $livreur->colis()
                ->with(['zone', 'commune', 'engin', 'packageColis'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('livreurs.colis', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de l\'historique des colis: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de l\'historique des colis.');
        }
    }

    /**
     * Afficher l'historique des livraisons d'un livreur
     */
    public function livraisonsHistory(Livreur $livreur)
    {
        try {
            $data['menu'] = 'livreurs';
            $data['title'] = 'Livraisons du Livreur - ' . $livreur->first_name . ' ' . $livreur->last_name;

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $data['livreur'] = $livreur;
            $data['livraisons'] = $livreur->livraisons()
                ->with(['colis.zone', 'colis.commune', 'marchand', 'boutique'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('livreurs.livraisons', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de l\'historique des livraisons: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de l\'historique des livraisons.');
        }
    }

    /**
     * Rechercher des livreurs
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $status = $request->get('status');

            $livreurs = Livreur::with(['engin.typeEngin', 'communes'])
                ->when($query, function($q) use ($query) {
                    $q->where(function($subQ) use ($query) {
                        $subQ->where('first_name', 'like', "%{$query}%")
                             ->orWhere('last_name', 'like', "%{$query}%")
                             ->orWhere('mobile', 'like', "%{$query}%")
                             ->orWhere('email', 'like', "%{$query}%");
                    });
                })
                ->when($status, function($q) use ($status) {
                    $q->where('status', $status);
                })
                ->orderBy('first_name')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $livreurs
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche de livreurs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * Basculer le statut d'un livreur
     */
    public function toggleStatus(Livreur $livreur)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            $newStatus = $livreur->status === 'actif' ? 'inactif' : 'actif';
            $livreur->update([
                'status' => $newStatus,
                'updated_by' => $user->id
            ]);

            Log::info('Statut du livreur modifié', [
                'livreur_id' => $livreur->id,
                'ancien_statut' => $livreur->status,
                'nouveau_statut' => $newStatus,
                'user_id' => $user->id
            ]);

            return redirect()->back()
                ->with('success', 'Statut du livreur mis à jour avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors du changement de statut.');
        }
    }
}
