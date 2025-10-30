<?php

namespace App\Http\Controllers;

use App\Models\Type_colis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TypeColisController extends Controller
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

        // Récupérer tous les types de colis avec pagination
        $typeColis = Type_colis::where('entreprise_id', $user->entreprise_id)
        ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data = [
            'title' => 'Types de Colis',
            'menu' => 'type_colis',
            'user' => $user,
            'typeColis' => $typeColis
        ];

        return view('type-colis.index', $data);
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
            'title' => 'Ajouter un Type de Colis',
            'menu' => 'type_colis',
            'user' => $user
        ];

        return view('type-colis.create', $data);
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
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u|unique:type_colis,libelle,NULL,id,deleted_at,NULL'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'libelle.unique' => 'Ce type de colis existe déjà.'
            ]);

            DB::beginTransaction();

            // Créer le type de colis
            $type_colis = Type_colis::create([
                'libelle' => $request->libelle,
                'created_by' => $user->id
            ]);

            // Log de l'action
            Log::info('Type de colis créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type_colis_id' => $type_colis->id,
                'libelle' => $type_colis->libelle
            ]);

            DB::commit();

            return redirect()->route('type-colis.index')
                ->with('success', 'Le type de colis a été créé avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du type de colis', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création du type de colis.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Type_colis $type_coli)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les relations
        $type_coli->load(['user', 'colis']);

        return view('type-colis.show', [
            'title' => 'Détails du Type de Colis',
            'menu' => 'type_colis',
            'user' => $user,
            'type_colis' => $type_coli
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Type_colis $type_coli)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        return view('type-colis.edit', [
            'title' => 'Modifier le Type de Colis',
            'menu' => 'type_colis',
            'user' => $user,
            'type_colis' => $type_coli
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Type_colis $type_coli)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            // Validation des données
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u|unique:type_colis,libelle,' . $type_coli->id . ',id,deleted_at,NULL'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'libelle.unique' => 'Ce type de colis existe déjà.'
            ]);

            DB::beginTransaction();

            // Mettre à jour le type de colis
            $type_coli->update([
                'libelle' => $request->libelle
            ]);

            // Log de l'action
            Log::info('Type de colis modifié', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type_colis_id' => $type_coli->id,
                'libelle' => $type_coli->libelle
            ]);

            DB::commit();

            return redirect()->route('type-colis.index')
                ->with('success', 'Le type de colis a été modifié avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du type de colis', [
                'user_id' => $user->id,
                'type_colis_id' => $type_coli->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la modification du type de colis.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type_colis $type_coli)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des colis associés
            if ($type_coli->colis()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer ce type de colis car il est associé à des colis.');
            }

            // Supprimer le type de colis (soft delete)
            $type_coli->delete();

            // Log de l'action
            Log::info('Type de colis supprimé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type_colis_id' => $type_coli->id,
                'libelle' => $type_coli->libelle
            ]);

            DB::commit();

            return redirect()->route('type-colis.index')
                ->with('success', 'Le type de colis a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du type de colis', [
                'user_id' => $user->id,
                'type_colis_id' => $type_coli->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du type de colis.');
        }
    }

    /**
     * Rechercher des types de colis
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

        $typeColis = Type_colis::where('libelle', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'libelle']);

        return response()->json($typeColis);
    }
}