<?php

namespace App\Http\Controllers;

use App\Models\Delais;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DelaisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Récupérer l'entreprise de l'utilisateur connecté
        $entreprise = Entreprise::where('created_by', $user->id)->first();
        $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise

        // Récupérer les délais de l'entreprise de l'utilisateur connecté
        $delais = Delais::with(['user', 'entreprise'])
            ->where('entreprise_id', $entrepriseId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data = [
            'title' => 'Délais',
            'menu' => 'delais',
            'user' => $user,
            'delais' => $delais
        ];

        return view('delais.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        $data = [
            'title' => 'Ajouter un Délai',
            'menu' => 'delais',
            'user' => $user
        ];

        return view('delais.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            // Validation des données
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u|unique:delais,libelle,NULL,id,deleted_at,NULL'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'libelle.unique' => 'Ce délai existe déjà.'
            ]);

            DB::beginTransaction();

            // Récupérer l'entreprise de l'utilisateur connecté
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise

            // Créer le délai
            $delai = Delais::create([
                'entreprise_id' => $entrepriseId,
                'libelle' => $request->libelle,
                'created_by' => $user->id
            ]);

            // Log de l'action
            Log::info('Délai créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'delai_id' => $delai->id,
                'libelle' => $delai->libelle
            ]);

            DB::commit();

            return redirect()->route('delais.index')
                ->with('success', 'Le délai a été créé avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du délai', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création du délai.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Delais $delai)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les relations
        $delai->load(['user', 'entreprise']);

        return view('delais.show', [
            'title' => 'Détails du Délai',
            'menu' => 'delais',
            'user' => $user,
            'delai' => $delai
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Delais $delai)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        return view('delais.edit', [
            'title' => 'Modifier le Délai',
            'menu' => 'delais',
            'user' => $user,
            'delai' => $delai
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Delais $delai)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            // Validation des données
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u|unique:delais,libelle,' . $delai->id . ',id,deleted_at,NULL'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'libelle.unique' => 'Ce délai existe déjà.'
            ]);

            DB::beginTransaction();

            // Mettre à jour le délai
            $delai->update([
                'libelle' => $request->libelle
            ]);

            // Log de l'action
            Log::info('Délai modifié', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'delai_id' => $delai->id,
                'libelle' => $delai->libelle
            ]);

            DB::commit();

            return redirect()->route('delais.index')
                ->with('success', 'Le délai a été modifié avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du délai', [
                'user_id' => $user->id,
                'delai_id' => $delai->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la modification du délai.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Delais $delai)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Note: Les délais ne sont pas directement liés aux colis via une clé étrangère
            // Aucune vérification nécessaire pour les colis associés

            // Supprimer le délai (soft delete)
            $delai->delete();

            // Log de l'action
            Log::info('Délai supprimé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'delai_id' => $delai->id,
                'libelle' => $delai->libelle
            ]);

            DB::commit();

            return redirect()->route('delais.index')
                ->with('success', 'Le délai a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du délai', [
                'user_id' => $user->id,
                'delai_id' => $delai->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du délai.');
        }
    }

    /**
     * Rechercher des délais
     */
    public function search(Request $request)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $user = Auth::user();

        // Récupérer l'entreprise de l'utilisateur connecté
        $entreprise = Entreprise::where('created_by', $user->id)->first();
        $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise

        $delais = Delais::where('entreprise_id', $entrepriseId)
            ->where('libelle', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'libelle']);

        return response()->json($delais);
    }
}
