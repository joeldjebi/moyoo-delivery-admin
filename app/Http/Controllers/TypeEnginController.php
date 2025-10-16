<?php

namespace App\Http\Controllers;

use App\Models\Type_engin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TypeEnginController extends Controller
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

        // Récupérer tous les types d'engins avec pagination
        $typeEngins = Type_engin::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data = [
            'title' => 'Types d\'Engins',
            'menu' => 'type_engins',
            'user' => $user,
            'typeEngins' => $typeEngins
        ];

        return view('type-engin.index', $data);
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
            'title' => 'Ajouter un Type d\'Engin',
            'menu' => 'type_engins',
            'user' => $user
        ];

        return view('type-engin.create', $data);
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
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u|unique:type_engins,libelle,NULL,id,deleted_at,NULL'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'libelle.unique' => 'Ce type d\'engin existe déjà.'
            ]);

            DB::beginTransaction();

            // Créer le type d'engin
            $type_engin = Type_engin::create([
                'libelle' => $request->libelle,
                'created_by' => $user->id
            ]);

            // Log de l'action
            Log::info('Type d\'engin créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type_engin_id' => $type_engin->id,
                'libelle' => $type_engin->libelle
            ]);

            DB::commit();

            return redirect()->route('type-engins.index')
                ->with('success', 'Le type d\'engin a été créé avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du type d\'engin', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création du type d\'engin.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Type_engin $type_engin)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les relations
        $type_engin->load(['user', 'engins']);

        $data = [
            'title' => 'Détails du Type d\'Engin',
            'menu' => 'type_engins',
            'user' => $user
        ];

        return view('type-engin.show', $data)->with('type_engin', $type_engin);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Type_engin $type_engin)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        $data = [
            'title' => 'Modifier le Type d\'Engin',
            'menu' => 'type_engins',
            'user' => $user
        ];

        return view('type-engin.edit', $data)->with('type_engin', $type_engin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Type_engin $type_engin)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            // Validation des données
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u|unique:type_engins,libelle,' . $type_engin->id . ',id,deleted_at,NULL'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
                'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'libelle.unique' => 'Ce type d\'engin existe déjà.'
            ]);

            DB::beginTransaction();

            // Mettre à jour le type d'engin
            $type_engin->update([
                'libelle' => $request->libelle
            ]);

            // Log de l'action
            Log::info('Type d\'engin modifié', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type_engin_id' => $type_engin->id,
                'libelle' => $type_engin->libelle
            ]);

            DB::commit();

            return redirect()->route('type-engins.index')
                ->with('success', 'Le type d\'engin a été modifié avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du type d\'engin', [
                'user_id' => $user->id,
                'type_engin_id' => $type_engin->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la modification du type d\'engin.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type_engin $type_engin)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des engins associés
            if ($type_engin->engins()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer ce type d\'engin car il est associé à des engins.');
            }

            // Supprimer le type d'engin (soft delete)
            $type_engin->delete();

            // Log de l'action
            Log::info('Type d\'engin supprimé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'type_engin_id' => $type_engin->id,
                'libelle' => $type_engin->libelle
            ]);

            DB::commit();

            return redirect()->route('type-engins.index')
                ->with('success', 'Le type d\'engin a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du type d\'engin', [
                'user_id' => $user->id,
                'type_engin_id' => $type_engin->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du type d\'engin.');
        }
    }

    /**
     * Rechercher des types d'engins
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

        $typeEngins = Type_engin::where('libelle', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'libelle']);

        return response()->json($typeEngins);
    }
}