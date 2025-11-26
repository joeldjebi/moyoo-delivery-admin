<?php

namespace App\Http\Controllers;

use App\Models\Engin;
use App\Models\Type_engin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EnginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();
        $engins = Engin::with(['typeEngin', 'user'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [
            'title' => 'Liste des Engins',
            'menu' => 'engins',
            'user' => $user,
            'engins' => $engins
        ];

        return view('engins.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();
        $typeEngins = Type_engin::active()->get();

        $data = [
            'title' => 'Créer un Engin',
            'menu' => 'engins',
            'user' => $user,
            'typeEngins' => $typeEngins
        ];

        return view('engins.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u',
                'marque' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u',
                'modele' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.0-9]+$/u',
                'couleur' => 'required|string|in:Blanc,Noir,Rouge,Bleu,Vert,Jaune,Orange,Violet,Rose,Gris,Marron,Beige,Argent,Or',
                'immatriculation' => 'required|string|max:255|unique:engins,immatriculation,NULL,id,deleted_at,NULL',
                'etat' => 'required|string|in:neuf,occasion,endommage',
                'status' => 'required|string|in:actif,inactif,maintenance',
                'type_engin_id' => 'required|exists:type_engins,id'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'marque.required' => 'La marque est obligatoire.',
                'marque.regex' => 'La marque ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'modele.required' => 'Le modèle est obligatoire.',
                'modele.regex' => 'Le modèle ne peut contenir que des lettres, chiffres, espaces, tirets, apostrophes et points.',
                'couleur.required' => 'La couleur est obligatoire.',
                'couleur.in' => 'Veuillez sélectionner une couleur valide dans la liste.',
                'immatriculation.required' => 'L\'immatriculation est obligatoire.',
                'immatriculation.unique' => 'Cette immatriculation existe déjà.',
                'etat.required' => 'L\'état est obligatoire.',
                'etat.in' => 'L\'état doit être neuf, occasion ou endommagé.',
                'status.required' => 'Le statut est obligatoire.',
                'status.in' => 'Le statut doit être actif, inactif ou maintenance.',
                'type_engin_id.required' => 'Le type d\'engin est obligatoire.',
                'type_engin_id.exists' => 'Le type d\'engin sélectionné n\'existe pas.'
            ]);

            DB::beginTransaction();

            $engin = Engin::create([
                'libelle' => $request->libelle,
                'marque' => $request->marque,
                'modele' => $request->modele,
                'couleur' => $request->couleur,
                'immatriculation' => $request->immatriculation,
                'etat' => $request->etat,
                'status' => $request->status,
                'type_engin_id' => $request->type_engin_id,
                'created_by' => $user->id
            ]);

            Log::info('Engin créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'engin_id' => $engin->id,
                'libelle' => $engin->libelle
            ]);

            DB::commit();

            return redirect()->route('engins.index')
                ->with('success', 'L\'engin a été créé avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'engin', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création de l\'engin.')
                ->withInput();
        }
    }

    /**
     * Store a newly created engin via API (AJAX)
     */
    public function storeApi(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez vous connecter pour accéder à cette page.'
            ], 401);
        }

        $user = Auth::user();

        try {
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u',
                'marque' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u',
                'modele' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.0-9]+$/u',
                'couleur' => 'required|string|in:Blanc,Noir,Rouge,Bleu,Vert,Jaune,Orange,Violet,Rose,Gris,Marron,Beige,Argent,Or',
                'immatriculation' => 'required|string|max:255|unique:engins,immatriculation,NULL,id,deleted_at,NULL',
                'etat' => 'required|string|in:neuf,occasion,endommage',
                'status' => 'required|string|in:actif,inactif,maintenance',
                'type_engin_id' => 'required|exists:type_engins,id'
            ]);

            DB::beginTransaction();

            $engin = Engin::create([
                'libelle' => $request->libelle,
                'marque' => $request->marque,
                'modele' => $request->modele,
                'couleur' => $request->couleur,
                'immatriculation' => $request->immatriculation,
                'etat' => $request->etat,
                'status' => $request->status,
                'type_engin_id' => $request->type_engin_id,
                'entreprise_id' => $user->entreprise_id,
                'created_by' => $user->id
            ]);

            $engin->load('typeEngin');

            Log::info('Engin créé via API', [
                'user_id' => $user->id,
                'engin_id' => $engin->id,
                'libelle' => $engin->libelle
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'L\'engin a été créé avec succès.',
                'engin' => [
                    'id' => $engin->id,
                    'libelle' => $engin->libelle,
                    'type_engin' => $engin->typeEngin ? $engin->typeEngin->libelle : null,
                    'display' => $engin->libelle . ($engin->typeEngin ? ' - ' . $engin->typeEngin->libelle : '')
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'engin via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'engin : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Engin $engin)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();
        $engin->load(['typeEngin', 'user', 'colis']);

        $data = [
            'title' => 'Détails de l\'Engin',
            'menu' => 'engins',
            'user' => $user,
            'engin' => $engin
        ];

        return view('engins.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Engin $engin)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();
        $typeEngins = Type_engin::active()->get();

        $data = [
            'title' => 'Modifier l\'Engin',
            'menu' => 'engins',
            'user' => $user,
            'engin' => $engin,
            'typeEngins' => $typeEngins
        ];

        return view('engins.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Engin $engin)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            $request->validate([
                'libelle' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u',
                'marque' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u',
                'modele' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\-\'\.0-9]+$/u',
                'couleur' => 'required|string|in:Blanc,Noir,Rouge,Bleu,Vert,Jaune,Orange,Violet,Rose,Gris,Marron,Beige,Argent,Or',
                'immatriculation' => 'required|string|max:255|unique:engins,immatriculation,' . $engin->id . ',id,deleted_at,NULL',
                'etat' => 'required|string|in:neuf,occasion,endommage',
                'status' => 'required|string|in:actif,inactif,maintenance',
                'type_engin_id' => 'required|exists:type_engins,id'
            ], [
                'libelle.required' => 'Le libellé est obligatoire.',
                'libelle.regex' => 'Le libellé ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'marque.required' => 'La marque est obligatoire.',
                'marque.regex' => 'La marque ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
                'modele.required' => 'Le modèle est obligatoire.',
                'modele.regex' => 'Le modèle ne peut contenir que des lettres, chiffres, espaces, tirets, apostrophes et points.',
                'couleur.required' => 'La couleur est obligatoire.',
                'couleur.in' => 'Veuillez sélectionner une couleur valide dans la liste.',
                'immatriculation.required' => 'L\'immatriculation est obligatoire.',
                'immatriculation.unique' => 'Cette immatriculation existe déjà.',
                'etat.required' => 'L\'état est obligatoire.',
                'etat.in' => 'L\'état doit être neuf, occasion ou endommagé.',
                'status.required' => 'Le statut est obligatoire.',
                'status.in' => 'Le statut doit être actif, inactif ou maintenance.',
                'type_engin_id.required' => 'Le type d\'engin est obligatoire.',
                'type_engin_id.exists' => 'Le type d\'engin sélectionné n\'existe pas.'
            ]);

            DB::beginTransaction();

            $engin->update([
                'libelle' => $request->libelle,
                'marque' => $request->marque,
                'modele' => $request->modele,
                'couleur' => $request->couleur,
                'immatriculation' => $request->immatriculation,
                'etat' => $request->etat,
                'status' => $request->status,
                'type_engin_id' => $request->type_engin_id
            ]);

            Log::info('Engin modifié', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'engin_id' => $engin->id,
                'libelle' => $engin->libelle
            ]);

            DB::commit();

            return redirect()->route('engins.index')
                ->with('success', 'L\'engin a été modifié avec succès.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification de l\'engin', [
                'user_id' => $user->id,
                'engin_id' => $engin->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la modification de l\'engin.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Engin $engin)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        try {
            // Vérifier s'il y a des colis associés
            if ($engin->colis()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cet engin car il est associé à des colis.');
            }

            DB::beginTransaction();

            $enginData = [
                'id' => $engin->id,
                'libelle' => $engin->libelle
            ];

            $engin->delete();

            Log::info('Engin supprimé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'engin_data' => $enginData
            ]);

            DB::commit();

            return redirect()->route('engins.index')
                ->with('success', 'L\'engin a été supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'engin', [
                'user_id' => $user->id,
                'engin_id' => $engin->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression de l\'engin.');
        }
    }

    /**
     * Search engins
     */
    public function search(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $engins = Engin::with(['typeEngin', 'user'])
            ->active()
            ->where(function($q) use ($query) {
                $q->where('libelle', 'like', "%{$query}%")
                  ->orWhere('marque', 'like', "%{$query}%")
                  ->orWhere('modele', 'like', "%{$query}%")
                  ->orWhere('immatriculation', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($engins);
    }
}