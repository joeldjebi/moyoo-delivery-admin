<?php

namespace App\Http\Controllers;

use App\Models\Historique_livraison;
use App\Models\PackageColis;
use App\Models\Livraison;
use App\Models\Colis;
use App\Models\Livreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HistoriqueLivraisonController extends Controller
{
    /**
     * Récupérer l'ID de l'entreprise de l'utilisateur connecté
     */
    private function getEntrepriseId()
    {
        $user = Auth::user();
        if (!$user) {
            return 1; // Valeur par défaut
        }

        // Récupérer l'entreprise via la table entreprises où created_by = user_id
        $entreprise = DB::table('entreprises')
            ->where('created_by', $user->id)
            ->first();

        return $entreprise ? $entreprise->id : 1; // Valeur par défaut si pas d'entreprise
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $data['menu'] = 'historique-livraisons';
            $data['title'] = 'Historique des Livraisons';

            $data['user'] = Auth::user();
            if (empty($data['user'])) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Construire la requête avec les filtres
            $query = Historique_livraison::with([
                'entreprise',
                'packageColis',
                'livraison',
                'colis',
                'livreur',
                'user'
            ])->byEntreprise($entrepriseId);

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filtre par livreur
            if ($request->filled('livreur_id')) {
                $query->where('livreur_id', $request->get('livreur_id'));
            }

            // Filtre par date
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            $data['historique_livraisons'] = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

            // Ajouter les données nécessaires pour les filtres
            $data['livreurs'] = Livreur::where('entreprise_id', $entrepriseId)
                ->where('status', 'actif')
                ->orderBy('first_name')
                ->get();

            $data['statuses'] = [
                'en_attente' => 'En attente',
                'en_cours' => 'En cours',
                'livre' => 'Livré',
                'annule_client' => 'Annulé par le client',
                'annule_livreur' => 'Annulé par le livreur',
                'annule_marchand' => 'Annulé par le marchand'
            ];

            return view('historique-livraisons.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'historique des livraisons: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la récupération de l\'historique des livraisons: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data['menu'] = 'historique-livraisons';
            $data['title'] = 'Ajouter un Historique de Livraison';

            $data['user'] = Auth::user();
            if (empty($data['user'])) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Récupérer les données nécessaires
            $data['package_colis'] = PackageColis::where('entreprise_id', $entrepriseId)
                ->orderBy('numero_package')
                ->get();
            $data['livraisons'] = Livraison::where('entreprise_id', $entrepriseId)
                ->orderBy('numero_de_livraison')
                ->get();
            $data['colis'] = Colis::where('entreprise_id', $entrepriseId)
                ->orderBy('code')
                ->get();
            $data['livreurs'] = Livreur::where('entreprise_id', $entrepriseId)
                ->where('status', 'actif')
                ->orderBy('first_name')
                ->get();

            $data['statuses'] = [
                'en_attente' => 'En attente',
                'en_cours' => 'En cours',
                'livre' => 'Livré',
                'annule_client' => 'Annulé par le client',
                'annule_livreur' => 'Annulé par le livreur',
                'annule_marchand' => 'Annulé par le marchand'
            ];

            return view('historique-livraisons.create', $data);
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
            if (empty($user)) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Validation des données
            $request->validate([
                'package_colis_id' => 'required|exists:package_colis,id',
                'livraison_id' => 'required|exists:livraisons,id',
                'status' => 'required|string|max:255',
                'colis_id' => 'required|exists:colis,id',
                'livreur_id' => 'required|exists:livreurs,id',
                'montant_a_encaisse' => 'required|integer|min:0',
                'prix_de_vente' => 'required|integer|min:0',
                'montant_de_la_livraison' => 'required|integer|min:0'
            ]);

            DB::beginTransaction();

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Créer l'historique de livraison
            $historiqueLivraison = Historique_livraison::create([
                'entreprise_id' => $entrepriseId,
                'package_colis_id' => $request->package_colis_id,
                'livraison_id' => $request->livraison_id,
                'status' => $request->status,
                'colis_id' => $request->colis_id,
                'livreur_id' => $request->livreur_id,
                'montant_a_encaisse' => $request->montant_a_encaisse,
                'prix_de_vente' => $request->prix_de_vente,
                'montant_de_la_livraison' => $request->montant_de_la_livraison,
                'created_by' => $user->id
            ]);

            DB::commit();

            Log::info('Historique de livraison créé avec succès', [
                'historique_id' => $historiqueLivraison->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('historique-livraisons.index')
                ->with('success', 'Historique de livraison créé avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'historique de livraison: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'historique de livraison.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Historique_livraison $historique_livraison)
    {
        try {
            $data['menu'] = 'historique-livraisons';
            $data['title'] = 'Détails de l\'Historique de Livraison';

            $data['user'] = Auth::user();
            if (empty($data['user'])) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier que l'historique appartient à l'entreprise de l'utilisateur
            $entrepriseId = $this->getEntrepriseId();
            if ($historique_livraison->entreprise_id !== $entrepriseId) {
                return redirect()->route('historique-livraisons.index')
                    ->with('error', 'Accès non autorisé à cet historique de livraison.');
            }

            $historique_livraison->load([
                'entreprise',
                'packageColis',
                'livraison',
                'colis',
                'livreur',
                'user'
            ]);

            $data['historique_livraison'] = $historique_livraison;

            return view('historique-livraisons.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'historique de livraison: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage de l\'historique de livraison.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Historique_livraison $historique_livraison)
    {
        try {
            $data['menu'] = 'historique-livraisons';
            $data['title'] = 'Modifier l\'Historique de Livraison';

            $data['user'] = Auth::user();
            if (empty($data['user'])) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier que l'historique appartient à l'entreprise de l'utilisateur
            $entrepriseId = $this->getEntrepriseId();
            if ($historique_livraison->entreprise_id !== $entrepriseId) {
                return redirect()->route('historique-livraisons.index')
                    ->with('error', 'Accès non autorisé à cet historique de livraison.');
            }

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            // Récupérer les données nécessaires
            $data['package_colis'] = PackageColis::where('entreprise_id', $entrepriseId)
                ->orderBy('numero_package')
                ->get();
            $data['livraisons'] = Livraison::where('entreprise_id', $entrepriseId)
                ->orderBy('numero_de_livraison')
                ->get();
            $data['colis'] = Colis::where('entreprise_id', $entrepriseId)
                ->orderBy('code')
                ->get();
            $data['livreurs'] = Livreur::where('entreprise_id', $entrepriseId)
                ->where('status', 'actif')
                ->orderBy('first_name')
                ->get();

            $data['statuses'] = [
                'en_attente' => 'En attente',
                'en_cours' => 'En cours',
                'livre' => 'Livré',
                'annule_client' => 'Annulé par le client',
                'annule_livreur' => 'Annulé par le livreur',
                'annule_marchand' => 'Annulé par le marchand'
            ];

            $data['historique_livraison'] = $historique_livraison;

            return view('historique-livraisons.edit', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire de modification: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Historique_livraison $historique_livraison)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier que l'historique appartient à l'entreprise de l'utilisateur
            $entrepriseId = $this->getEntrepriseId();
            if ($historique_livraison->entreprise_id !== $entrepriseId) {
                return redirect()->route('historique-livraisons.index')
                    ->with('error', 'Accès non autorisé à cet historique de livraison.');
            }

            // Validation des données
            $request->validate([
                'package_colis_id' => 'required|exists:package_colis,id',
                'livraison_id' => 'required|exists:livraisons,id',
                'status' => 'required|string|max:255',
                'colis_id' => 'required|exists:colis,id',
                'livreur_id' => 'required|exists:livreurs,id',
                'montant_a_encaisse' => 'required|integer|min:0',
                'prix_de_vente' => 'required|integer|min:0',
                'montant_de_la_livraison' => 'required|integer|min:0'
            ]);

            DB::beginTransaction();

            // Mettre à jour l'historique de livraison
            $historique_livraison->update([
                'package_colis_id' => $request->package_colis_id,
                'livraison_id' => $request->livraison_id,
                'status' => $request->status,
                'colis_id' => $request->colis_id,
                'livreur_id' => $request->livreur_id,
                'montant_a_encaisse' => $request->montant_a_encaisse,
                'prix_de_vente' => $request->prix_de_vente,
                'montant_de_la_livraison' => $request->montant_de_la_livraison
            ]);

            DB::commit();

            Log::info('Historique de livraison modifié avec succès', [
                'historique_id' => $historique_livraison->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('historique-livraisons.index')
                ->with('success', 'Historique de livraison modifié avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification de l\'historique de livraison: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification de l\'historique de livraison.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Historique_livraison $historique_livraison)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Vérifier que l'historique appartient à l'entreprise de l'utilisateur
            $entrepriseId = $this->getEntrepriseId();
            if ($historique_livraison->entreprise_id !== $entrepriseId) {
                return redirect()->route('historique-livraisons.index')
                    ->with('error', 'Accès non autorisé à cet historique de livraison.');
            }

            DB::beginTransaction();

            // Supprimer l'historique de livraison (soft delete)
            $historique_livraison->delete();

            DB::commit();

            Log::info('Historique de livraison supprimé avec succès', [
                'historique_id' => $historique_livraison->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('historique-livraisons.index')
                ->with('success', 'Historique de livraison supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'historique de livraison: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression de l\'historique de livraison.');
        }
    }

    /**
     * Recherche d'historiques de livraisons
     */
    public function search(Request $request)
    {
        try {
            $user = Auth::user();
            if (empty($user)) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Récupérer l'ID de l'entreprise de l'utilisateur connecté
            $entrepriseId = $this->getEntrepriseId();

            $query = $request->get('q');
            $historique_livraisons = Historique_livraison::byEntreprise($entrepriseId)
                ->with(['livreur', 'colis', 'livraison'])
                ->where(function ($q) use ($query) {
                    $q->where('status', 'like', "%{$query}%")
                      ->orWhereHas('livreur', function ($subQ) use ($query) {
                          $subQ->where('first_name', 'like', "%{$query}%")
                               ->orWhere('last_name', 'like', "%{$query}%");
                      })
                      ->orWhereHas('colis', function ($subQ) use ($query) {
                          $subQ->where('code', 'like', "%{$query}%")
                               ->orWhere('nom_client', 'like', "%{$query}%");
                      });
                })
                ->limit(10)
                ->get();

            return response()->json($historique_livraisons);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche d\'historiques de livraisons: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la recherche'], 500);
        }
    }
}
