<?php

namespace App\Http\Controllers;

use App\Models\Poid;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoidController extends Controller
{
    /**
     * Afficher la liste des poids
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

        // Récupérer les poids de l'entreprise de l'utilisateur connecté
        $poids = Poid::with(['user', 'entreprise'])
            ->where('entreprise_id', $entrepriseId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('poids.index', [
            'title' => 'Poids',
            'menu' => 'poids',
            'user' => $user,
            'poids' => $poids
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

        return view('poids.create', [
            'title' => 'Nouveau Poids',
            'menu' => 'poids',
            'user' => Auth::user()
        ]);
    }

    /**
     * Enregistrer un nouveau poids
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
                'unique:poids,libelle,NULL,id,entreprise_id,' . ($user->entreprise_id ?? 1)
            ]
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.regex' => 'Le libellé contient des caractères non autorisés.',
            'libelle.unique' => 'Ce libellé existe déjà pour votre entreprise.'
        ]);

        try {
            DB::beginTransaction();

            // Récupérer l'entreprise de l'utilisateur connecté
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise

            // Créer le poids
            $poid = Poid::create([
                'entreprise_id' => $entrepriseId,
                'libelle' => $request->libelle,
                'created_by' => $user->id
            ]);

            // Log de l'action
            Log::info('Poids créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'poid_id' => $poid->id,
                'libelle' => $poid->libelle
            ]);

            DB::commit();

            return redirect()->route('poids.index')
                ->with('success', 'Le poids a été créé avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du poids', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du poids.');
        }
    }

    /**
     * Afficher les détails d'un poids
     */
    public function show(Poid $poid)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Charger les relations
        $poid->load(['user', 'entreprise']);

        return view('poids.show', [
            'title' => 'Détails du Poids',
            'menu' => 'poids',
            'user' => $user,
            'poid' => $poid
        ]);
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Poid $poid)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        return view('poids.edit', [
            'title' => 'Modifier le Poids',
            'menu' => 'poids',
            'user' => Auth::user(),
            'poid' => $poid
        ]);
    }

    /**
     * Mettre à jour un poids
     */
    public function update(Request $request, Poid $poid)
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
                'unique:poids,libelle,' . $poid->id . ',id,entreprise_id,' . $poid->entreprise_id
            ]
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.regex' => 'Le libellé contient des caractères non autorisés.',
            'libelle.unique' => 'Ce libellé existe déjà pour votre entreprise.'
        ]);

        try {
            DB::beginTransaction();

            // Mettre à jour le poids
            $poid->update([
                'libelle' => $request->libelle
            ]);

            // Log de l'action
            Log::info('Poids modifié', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'poid_id' => $poid->id,
                'libelle' => $poid->libelle
            ]);

            DB::commit();

            return redirect()->route('poids.show', $poid->id)
                ->with('success', 'Le poids a été modifié avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification du poids', [
                'user_id' => $user->id,
                'poid_id' => $poid->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la modification du poids.');
        }
    }

    /**
     * Supprimer un poids
     */
    public function destroy(Poid $poid)
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des colis associés
            if ($poid->colis()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer ce poids car il est associé à des colis.');
            }

            // Supprimer le poids (soft delete)
            $poid->delete();

            // Log de l'action
            Log::info('Poids supprimé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'poid_id' => $poid->id,
                'libelle' => $poid->libelle
            ]);

            DB::commit();

            return redirect()->route('poids.index')
                ->with('success', 'Le poids a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du poids', [
                'user_id' => $user->id,
                'poid_id' => $poid->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du poids.');
        }
    }

    /**
     * Rechercher des poids
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

        $poids = Poid::where('entreprise_id', $entrepriseId)
            ->where('libelle', 'like', "%{$query}%")
            ->with(['user', 'entreprise'])
            ->orderBy('libelle')
            ->limit(10)
            ->get();

        return response()->json($poids);
    }
}