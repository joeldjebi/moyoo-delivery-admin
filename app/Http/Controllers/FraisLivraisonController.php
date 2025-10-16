<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FraisLivraison;
use App\Models\HistoriqueFraisLivraison;
use App\Models\Entreprise;
use App\Models\Commune;

class FraisLivraisonController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $fraisLivraisons = FraisLivraison::byEntreprise($entrepriseId)
            ->with(['createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $data = [
            'title' => 'Frais de Livraison',
            'menu' => 'frais-livraisons',
            'fraisLivraisons' => $fraisLivraisons
        ];

        return view('frais-livraisons.index', $data);
    }

    public function create()
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $communes = Commune::all();

        $data = [
            'title' => 'Nouveau Frais de Livraison',
            'menu' => 'frais-livraisons',
            'communes' => $communes,
            'entrepriseId' => $entrepriseId
        ];

        return view('frais-livraisons.create', $data);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'montant' => 'required|numeric|min:0',
            'type_frais' => 'required|in:fixe,pourcentage,par_km,par_colis',
            'zone_applicable' => 'required|in:toutes,urbain,rural,specifique',
            'zones_specifiques' => 'nullable|array',
            'zones_specifiques.*' => 'exists:communes,id',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'actif' => 'boolean'
        ]);

        $fraisLivraison = FraisLivraison::create([
            'libelle' => $request->libelle,
            'description' => $request->description,
            'montant' => $request->montant,
            'type_frais' => $request->type_frais,
            'zone_applicable' => $request->zone_applicable,
            'zones_specifiques' => $request->zones_specifiques,
            'actif' => $request->has('actif'),
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'entreprise_id' => $entrepriseId,
            'created_by' => $user->id
        ]);

        // Enregistrer dans l'historique
        $this->createHistorique($fraisLivraison, 'creation', null, $fraisLivraison->toArray(), $user->id, $entrepriseId);

        return redirect()->route('frais-livraisons.index')
            ->with('success', 'Frais de livraison créé avec succès.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $fraisLivraison = FraisLivraison::byEntreprise($entrepriseId)
            ->with(['createdBy', 'historique.user'])
            ->findOrFail($id);

        $data = [
            'title' => 'Détails du Frais de Livraison',
            'menu' => 'frais-livraisons',
            'fraisLivraison' => $fraisLivraison
        ];

        return view('frais-livraisons.show', $data);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $fraisLivraison = FraisLivraison::byEntreprise($entrepriseId)->findOrFail($id);
        $communes = Commune::all();

        $data = [
            'title' => 'Modifier le Frais de Livraison',
            'menu' => 'frais-livraisons',
            'fraisLivraison' => $fraisLivraison,
            'communes' => $communes,
            'entrepriseId' => $entrepriseId
        ];

        return view('frais-livraisons.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $fraisLivraison = FraisLivraison::byEntreprise($entrepriseId)->findOrFail($id);

        $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'montant' => 'required|numeric|min:0',
            'type_frais' => 'required|in:fixe,pourcentage,par_km,par_colis',
            'zone_applicable' => 'required|in:toutes,urbain,rural,specifique',
            'zones_specifiques' => 'nullable|array',
            'zones_specifiques.*' => 'exists:communes,id',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'actif' => 'boolean'
        ]);

        $donneesAvant = $fraisLivraison->toArray();

        $fraisLivraison->update([
            'libelle' => $request->libelle,
            'description' => $request->description,
            'montant' => $request->montant,
            'type_frais' => $request->type_frais,
            'zone_applicable' => $request->zone_applicable,
            'zones_specifiques' => $request->zones_specifiques,
            'actif' => $request->has('actif'),
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin
        ]);

        // Enregistrer dans l'historique
        $this->createHistorique($fraisLivraison, 'modification', null, $fraisLivraison->toArray(), $user->id, $entrepriseId, $donneesAvant);

        return redirect()->route('frais-livraisons.index')
            ->with('success', 'Frais de livraison modifié avec succès.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $fraisLivraison = FraisLivraison::byEntreprise($entrepriseId)->findOrFail($id);

        // Enregistrer dans l'historique avant suppression
        $this->createHistorique($fraisLivraison, 'suppression', null, null, $user->id, $entrepriseId, $fraisLivraison->toArray());

        $fraisLivraison->delete();

        return redirect()->route('frais-livraisons.index')
            ->with('success', 'Frais de livraison supprimé avec succès.');
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $query = $request->get('q');
        $type = $request->get('type');
        $zone = $request->get('zone');
        $actif = $request->get('actif');

        $fraisLivraisons = FraisLivraison::byEntreprise($entrepriseId)
            ->with(['createdBy']);

        if ($query) {
            $fraisLivraisons->where(function($q) use ($query) {
                $q->where('libelle', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if ($type) {
            $fraisLivraisons->where('type_frais', $type);
        }

        if ($zone) {
            $fraisLivraisons->where('zone_applicable', $zone);
        }

        if ($actif !== null) {
            $fraisLivraisons->where('actif', $actif);
        }

        $results = $fraisLivraisons->orderBy('created_at', 'desc')->limit(20)->get();

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    private function createHistorique($fraisLivraison, $typeOperation, $colisId, $donneesApres, $userId, $entrepriseId, $donneesAvant = null)
    {
        $description = $this->getDescriptionOperation($typeOperation, $fraisLivraison);

        HistoriqueFraisLivraison::create([
            'frais_livraison_id' => $fraisLivraison->id,
            'colis_id' => $colisId,
            'type_operation' => $typeOperation,
            'montant_avant' => $donneesAvant['montant'] ?? null,
            'montant_apres' => $donneesApres['montant'] ?? null,
            'description_operation' => $description,
            'donnees_avant' => $donneesAvant,
            'donnees_apres' => $donneesApres,
            'entreprise_id' => $entrepriseId,
            'user_id' => $userId,
            'date_operation' => now()
        ]);
    }

    private function getDescriptionOperation($typeOperation, $fraisLivraison)
    {
        switch ($typeOperation) {
            case 'creation':
                return "Création du frais de livraison '{$fraisLivraison->libelle}'";
            case 'modification':
                return "Modification du frais de livraison '{$fraisLivraison->libelle}'";
            case 'suppression':
                return "Suppression du frais de livraison '{$fraisLivraison->libelle}'";
            case 'application':
                return "Application du frais de livraison '{$fraisLivraison->libelle}'";
            default:
                return "Opération sur le frais de livraison '{$fraisLivraison->libelle}'";
        }
    }
}
