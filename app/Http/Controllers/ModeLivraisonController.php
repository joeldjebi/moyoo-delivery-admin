<?php

namespace App\Http\Controllers;

use App\Models\Mode_livraison;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModeLivraisonController extends Controller
{
    /**
     * Afficher la liste des modes de livraison
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

        // Récupérer les modes de livraison de l'entreprise de l'utilisateur connecté
        $modeLivraisons = Mode_livraison::with(['user', 'entreprise'])
            ->where('entreprise_id', $entrepriseId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('mode-livraisons.index', [
            'title' => 'Modes de Livraison',
            'menu' => 'mode-livraisons',
            'user' => $user,
            'modeLivraisons' => $modeLivraisons
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        return view('mode-livraisons.create', [
            'title' => 'Nouveau Mode de Livraison',
            'menu' => 'mode-livraisons',
            'user' => Auth::user()
        ]);
    }

    /**
     * Enregistrer un nouveau mode de livraison
     */
    public function store(Request $request)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Validation des données
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_àâäéèêëïîôöùûüÿçñÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇÑ]+$/',
                'unique:mode_livraisons,libelle,NULL,id,entreprise_id,' . ($user->entreprise_id ?? 1)
            ],
            'description' => 'nullable|string|max:300'
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.regex' => 'Le libellé contient des caractères non autorisés.',
            'libelle.unique' => 'Ce libellé existe déjà pour votre entreprise.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 300 caractères.'
        ]);

        try {
            DB::beginTransaction();

            // Récupérer l'entreprise de l'utilisateur connecté
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise

            // Créer le mode de livraison
            $modeLivraison = Mode_livraison::create([
                'entreprise_id' => $entrepriseId,
                'libelle' => $request->libelle,
                'description' => $request->description,
                'created_by' => $user->id
            ]);

            // Log de l'action
            Log::info('Mode de livraison créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'mode_livraison_id' => $modeLivraison->id,
                'libelle' => $modeLivraison->libelle
            ]);

            DB::commit();

            return redirect()->route('mode-livraisons.index')
                ->with('success', 'Le mode de livraison a été créé avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du mode de livraison', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du mode de livraison.');
        }
    }

    /**
     * Afficher les détails d'un mode de livraison
     */
    public function show(Mode_livraison $modeLivraison)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les relations
        $modeLivraison->load(['user', 'entreprise']);

        return view('mode-livraisons.show', [
            'title' => 'Détails du Mode de Livraison',
            'menu' => 'mode-livraisons',
            'user' => $user,
            'modeLivraison' => $modeLivraison
        ]);
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Mode_livraison $modeLivraison)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        return view('mode-livraisons.edit', [
            'title' => 'Modifier le Mode de Livraison',
            'menu' => 'mode-livraisons',
            'user' => Auth::user(),
            'modeLivraison' => $modeLivraison
        ]);
    }

    /**
     * Mettre à jour un mode de livraison
     */
    public function update(Request $request, Mode_livraison $modeLivraison)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Validation des données
        $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_àâäéèêëïîôöùûüÿçñÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇÑ]+$/',
                'unique:mode_livraisons,libelle,' . $modeLivraison->id . ',id,entreprise_id,' . $modeLivraison->entreprise_id
            ],
            'description' => 'nullable|string|max:300'
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.regex' => 'Le libellé contient des caractères non autorisés.',
            'libelle.unique' => 'Ce libellé existe déjà pour votre entreprise.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 300 caractères.'
        ]);

        try {
            DB::beginTransaction();

            // Mettre à jour le mode de livraison
            $modeLivraison->update([
                'libelle' => $request->libelle,
                'description' => $request->description
            ]);

            // Log de l'action
            Log::info('Mode de livraison modifié', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'mode_livraison_id' => $modeLivraison->id,
                'libelle' => $modeLivraison->libelle
            ]);

            DB::commit();

            return redirect()->route('mode-livraisons.show', $modeLivraison->id)
                ->with('success', 'Le mode de livraison a été modifié avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du mode de livraison', [
                'user_id' => $user->id,
                'mode_livraison_id' => $modeLivraison->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la modification du mode de livraison.');
        }
    }

    /**
     * Supprimer un mode de livraison
     */
    public function destroy(Mode_livraison $modeLivraison)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des colis associés
            if ($modeLivraison->colis()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer ce mode de livraison car il est associé à des colis.');
            }

            // Supprimer le mode de livraison (soft delete)
            $modeLivraison->delete();

            // Log de l'action
            Log::info('Mode de livraison supprimé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'mode_livraison_id' => $modeLivraison->id,
                'libelle' => $modeLivraison->libelle
            ]);

            DB::commit();

            return redirect()->route('mode-livraisons.index')
                ->with('success', 'Le mode de livraison a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du mode de livraison', [
                'user_id' => $user->id,
                'mode_livraison_id' => $modeLivraison->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du mode de livraison.');
        }
    }

    /**
     * Rechercher des modes de livraison
     */
    public function search(Request $request)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $user = Auth::user();
        $query = $request->get('q', '');

        // Récupérer l'entreprise de l'utilisateur connecté
        $entreprise = Entreprise::where('created_by', $user->id)->first();
        $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise

        $modeLivraisons = Mode_livraison::where('entreprise_id', $entrepriseId)
            ->where('libelle', 'like', "%{$query}%")
            ->with(['user', 'entreprise'])
            ->orderBy('libelle')
            ->limit(10)
            ->get();

        return response()->json($modeLivraisons);
    }
}
