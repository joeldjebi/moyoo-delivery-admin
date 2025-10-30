<?php

namespace App\Http\Controllers;

use App\Models\TarifLivraison;
use App\Models\Commune;
use App\Models\Type_engin;
use App\Models\Engin;
use App\Models\Mode_livraison;
use App\Models\Entreprise;
use App\Models\Poid;
use App\Models\Temp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TarifLivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $data['menu'] = 'tarifs';
            $data['title'] = 'Gestion des Tarifs de Livraison';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer l'entreprise de l'utilisateur connecté
            $entreprise = Entreprise::getEntrepriseByUser(Auth::id());
            if (!$entreprise) {
                return redirect()->route('entreprise.create')->with('error', 'Veuillez d\'abord créer votre entreprise pour accéder aux tarifs.');
            }

            // Construire la requête avec les filtres (seulement pour l'entreprise de l'utilisateur)
            $query = TarifLivraison::with(['entreprise', 'communeDepart', 'commune', 'typeEngin', 'modeLivraison', 'poids', 'temp', 'createdBy'])
                                  ->where('entreprise_id', $entreprise->id);

            // Filtre par commune de départ
            if ($request->filled('commune_depart_id')) {
                $query->where('commune_depart_id', $request->get('commune_depart_id'));
            }

            // Filtre par commune de destination
            if ($request->filled('commune_id')) {
                $query->where('commune_id', $request->get('commune_id'));
            }

            // Filtre par type d'engin
            if ($request->filled('type_engin_id')) {
                $query->where('type_engin_id', $request->get('type_engin_id'));
            }

            // Filtre par mode de livraison
            if ($request->filled('mode_livraison_id')) {
                $query->where('mode_livraison_id', $request->get('mode_livraison_id'));
            }

            // Filtre par poids
            if ($request->filled('poids_id')) {
                $query->where('poids_id', $request->get('poids_id'));
            }

            // Filtre par période temporelle
            if ($request->filled('temp_id')) {
                $query->where('temp_id', $request->get('temp_id'));
            }

            // Filtre par montant minimum
            if ($request->filled('amount_min')) {
                $query->where('amount', '>=', $request->get('amount_min'));
            }

            // Filtre par montant maximum
            if ($request->filled('amount_max')) {
                $query->where('amount', '<=', $request->get('amount_max'));
            }

            // Gestion de l'export CSV
            if ($request->has('export') && $request->get('export') === 'csv') {
                return $this->exportToCsv($query->get());
            }

            $data['tarifs'] = $query->orderBy('commune_depart_id')->orderBy('commune_id')->orderBy('amount')->paginate(15)->appends($request->query());

            // Données pour les filtres
            // Commune de départ = commune de l'entreprise
            $data['communeDepart'] = Commune::with('entreprise')->find($entreprise->commune_id);
            // Communes de destination: toutes les communes disponibles
            $data['allCommunes'] = Commune::orderBy('libelle')->get();
            $data['typeEngins'] = Type_engin::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();
            $data['modeLivraisons'] = Mode_livraison::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();
            $data['poids'] = Poid::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();
            $data['temps'] = Temp::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();

            return view('tarifs.index', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des tarifs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la récupération des tarifs.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data['menu'] = 'tarifs';
            $data['title'] = 'Nouveau Tarif de Livraison';

            $data['user'] = Auth::user();
            if(empty($data['user'])){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Récupérer l'entreprise de l'utilisateur connecté
            $entreprise = Entreprise::getEntrepriseByUser(Auth::id());
            if (!$entreprise) {
                return redirect()->route('entreprise.create')->with('error', 'Veuillez d\'abord créer votre entreprise pour accéder aux tarifs.');
            }

            $data['communes'] = Commune::where('id', $entreprise->commune_id)
            ->orderBy('libelle')
            ->with('entreprise')
            ->get();
            $data['typeEngins'] = Type_engin::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();
            $data['modeLivraisons'] = Mode_livraison::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();
            $data['poids'] = Poid::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();
            $data['temps'] = Temp::where('entreprise_id', $entreprise->id)
            ->orderBy('libelle')->get();

            return view('tarifs.create', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire de création: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage du formulaire.' . $e->getMessage());
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

            // Validation des données
            $request->validate([
                'commune_depart_id' => 'required|exists:communes,id',
                'commune_id' => 'required|exists:communes,id',
                'type_engin_id' => 'required|exists:type_engins,id',
                'mode_livraison_id' => 'required|exists:mode_livraisons,id',
                'poids_id' => 'required|exists:poids,id',
                'temp_id' => 'required|exists:temps,id',
                'amount' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Entreprise et commune de départ (déduites de l'utilisateur)
            $entreprise = Entreprise::getEntrepriseByUser($user->id);
            if (!$entreprise) {
                return redirect()->route('entreprise.create')->with('error', 'Veuillez d\'abord créer votre entreprise.');
            }

            // Vérifier si un tarif existe déjà pour cette combinaison
            $existingTarif = TarifLivraison::where('entreprise_id', $entreprise->id)
                                         ->where('commune_depart_id', $entreprise->commune_id)
                                         ->where('commune_id', $request->commune_id)
                                         ->where('type_engin_id', $request->type_engin_id)
                                         ->where('mode_livraison_id', $request->mode_livraison_id)
                                         ->where('poids_id', $request->poids_id)
                                         ->where('temp_id', $request->temp_id)
                                         ->first();

            if ($existingTarif) {
                return redirect()->back()
                               ->with('error', 'Un tarif existe déjà pour cette combinaison.')
                               ->withInput();
            }

            // Créer le tarif
            $tarif = TarifLivraison::create([
                'entreprise_id' => $entreprise->id,
                'commune_depart_id' => $entreprise->commune_id,
                'commune_id' => $request->commune_id,
                'type_engin_id' => $request->type_engin_id,
                'mode_livraison_id' => $request->mode_livraison_id,
                'poids_id' => $request->poids_id,
                'temp_id' => $request->temp_id,
                'amount' => $request->amount,
                'created_by' => $user->id
            ]);

            DB::commit();

            Log::info('Tarif de livraison créé avec succès', [
                'tarif_id' => $tarif->id,
                'amount' => $tarif->amount,
                'user_id' => $user->id
            ]);

            return redirect()->route('tarifs.index')
                           ->with('success', 'Tarif de livraison créé avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du tarif: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Erreur lors de la création du tarif.')
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TarifLivraison $tarif)
    {
        try {
            $data['menu'] = 'tarifs';
            $data['title'] = 'Détails du Tarif';
            $data['tarif'] = $tarif->load(['commune', 'typeEngin', 'modeLivraison', 'poids', 'temp', 'createdBy']);

            return view('tarifs.show', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du tarif: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage du tarif.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TarifLivraison $tarif)
    {
        try {
            $data['menu'] = 'tarifs';
            $data['title'] = 'Modifier le Tarif';
            $data['tarif'] = $tarif;

            $data['communes'] = Commune::orderBy('libelle')->get();
            $data['typeEngins'] = Type_engin::orderBy('libelle')->get();
            $data['modeLivraisons'] = Mode_livraison::orderBy('libelle')->get();
            $data['poids'] = Poid::orderBy('libelle')->get();
            $data['temps'] = Temp::orderBy('libelle')->get();

            return view('tarifs.edit', $data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'affichage du formulaire.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TarifLivraison $tarif)
    {
        try {
            $user = Auth::user();
            if(empty($user)){
                return redirect()->route('auth.login')
                    ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
            }

            // Validation des données
            $request->validate([
                'commune_id' => 'required|exists:communes,id',
                'type_engin_id' => 'required|exists:type_engins,id',
                'mode_livraison_id' => 'required|exists:mode_livraisons,id',
                'poids_id' => 'required|exists:poids,id',
                'temp_id' => 'required|exists:temps,id',
                'amount' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Vérifier si un autre tarif existe déjà pour cette combinaison (contexte entreprise + commune de départ)
            $entreprise = Entreprise::getEntrepriseByUser($user->id);
            // $entreprise peut être null si supprimée, on garde une vérification douce
            $existingTarif = TarifLivraison::when($entreprise, function($q) use ($entreprise) {
                                            return $q->where('entreprise_id', $entreprise->id)
                                                     ->where('commune_depart_id', $entreprise->commune_id);
                                        })
                                         ->where('commune_id', $request->commune_id)
                                         ->where('type_engin_id', $request->type_engin_id)
                                         ->where('mode_livraison_id', $request->mode_livraison_id)
                                         ->where('poids_id', $request->poids_id)
                                         ->where('temp_id', $request->temp_id)
                                         ->where('id', '!=', $tarif->id)
                                         ->first();

            if ($existingTarif) {
                return redirect()->back()
                               ->with('error', 'Un autre tarif existe déjà pour cette combinaison.')
                               ->withInput();
            }

            // Mettre à jour le tarif
            $tarif->update([
                'commune_id' => $request->commune_id,
                'type_engin_id' => $request->type_engin_id,
                'mode_livraison_id' => $request->mode_livraison_id,
                'poids_id' => $request->poids_id,
                'temp_id' => $request->temp_id,
                'amount' => $request->amount
            ]);

            DB::commit();

            Log::info('Tarif de livraison mis à jour avec succès', [
                'tarif_id' => $tarif->id,
                'amount' => $tarif->amount,
                'user_id' => $user->id
            ]);

            return redirect()->route('tarifs.index')
                           ->with('success', 'Tarif de livraison mis à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withErrors($e->validator)
                           ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du tarif: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Erreur lors de la mise à jour du tarif.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TarifLivraison $tarif)
    {
        try {
            DB::beginTransaction();

            $tarif->delete();

            DB::commit();

            Log::info('Tarif de livraison supprimé avec succès', [
                'tarif_id' => $tarif->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('tarifs.index')
                           ->with('success', 'Tarif de livraison supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du tarif: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression du tarif.');
        }
    }

    /**
     * API pour calculer le coût de livraison
     */
    public function calculateCost(Request $request)
    {
        \Log::info('🚀 DÉBUT CALCUL COÛT API', [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        try {
            // Nettoyer les valeurs vides avant validation
            $request->merge([
                'engin_id' => $request->engin_id ?: null,
                'temp_id' => $request->temp_id ?: null
            ]);

            $request->validate([
                'commune_id' => 'required|exists:communes,id',
                'engin_id' => 'nullable|exists:engins,id',
                'mode_livraison_id' => 'required|exists:mode_livraisons,id',
                'poids_id' => 'required|exists:poids,id',
                'temp_id' => 'nullable|exists:temps,id'
            ]);

            \Log::info('✅ VALIDATION RÉUSSIE', $request->all());

            // Récupérer l'entreprise de l'utilisateur connecté
            $entreprise = Entreprise::getEntrepriseByUser(auth()->id());
            \Log::info('🏢 ENTREPRISE RÉCUPÉRÉE', [
                'entreprise' => $entreprise ? $entreprise->toArray() : null,
                'user_id' => auth()->id()
            ]);

            if (!$entreprise) {
                \Log::error('❌ AUCUNE ENTREPRISE TROUVÉE', ['user_id' => auth()->id()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune entreprise trouvée pour cet utilisateur.'
                ], 400);
            }

            // Récupérer le type d'engin (optionnel)
            $engin = null;
            $typeEnginId = null;

            if ($request->engin_id) {
                $engin = Engin::find($request->engin_id);
                \Log::info('🚛 ENGIN RÉCUPÉRÉ', [
                    'engin_id' => $request->engin_id,
                    'engin' => $engin ? $engin->toArray() : null
                ]);

                if (!$engin) {
                    \Log::error('❌ ENGIN INTROUVABLE', ['engin_id' => $request->engin_id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Engin introuvable.'
                    ], 400);
                }

                $typeEnginId = $engin->type_engin_id;
            } else {
                \Log::info('🚛 AUCUN ENGIN FOURNI - Utilisation du type d\'engin par défaut');
                // Utiliser un type d'engin par défaut ou le premier disponible
                $defaultTypeEngin = Type_engin::first();
                $typeEnginId = $defaultTypeEngin ? $defaultTypeEngin->id : null;
            }

            // Utiliser la période temporelle fournie ou déterminer la période actuelle
            $tempId = $request->temp_id;
            $temp = null;
            if (!$tempId) {
                $temp = Temp::getCurrentTemp();
                $tempId = $temp ? $temp->id : null;
            } else {
                // Récupérer l'objet temp si temp_id est fourni
                $temp = Temp::find($tempId);
            }

            \Log::info('⏰ PÉRIODE TEMPORELLE', [
                'temp_id_request' => $request->temp_id,
                'temp_id_final' => $tempId,
                'temp_object' => $temp ? $temp->toArray() : null
            ]);

            $cost = TarifLivraison::calculateDeliveryCost(
                $entreprise->id,
                $entreprise->commune_id,
                $request->commune_id,
                $typeEnginId,
                $request->mode_livraison_id,
                $request->poids_id,
                $tempId
            );

            \Log::info('💰 COÛT CALCULÉ', [
                'cost' => $cost,
                'parameters' => [
                    'entreprise_id' => $entreprise->id,
                    'commune_depart_id' => $entreprise->commune_id,
                    'commune_id' => $request->commune_id,
                    'type_engin_id' => $typeEnginId,
                    'mode_livraison_id' => $request->mode_livraison_id,
                    'poids_id' => $request->poids_id,
                    'temp_id' => $tempId
                ]
            ]);

            $response = [
                'success' => true,
                'cost' => $cost,
                'formatted_cost' => number_format($cost, 0, ',', ' ') . ' FCFA'
            ];

            \Log::info('📤 RÉPONSE API', $response);

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR CALCUL COÛT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du coût: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter les tarifs en CSV
     */
    private function exportToCsv($tarifs)
    {
        $filename = 'tarifs_livraison_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($tarifs) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'Entreprise',
                'Commune de Départ',
                'Commune de Destination',
                'Type d\'Engin',
                'Mode de Livraison',
                'Poids',
                'Période',
                'Montant (FCFA)',
                'Créé le'
            ]);

            // Données
            foreach ($tarifs as $tarif) {
                fputcsv($file, [
                    $tarif->entreprise->name ?? 'N/A',
                    $tarif->communeDepart->libelle ?? 'N/A',
                    $tarif->commune->libelle ?? 'N/A',
                    $tarif->typeEngin->libelle ?? 'N/A',
                    $tarif->modeLivraison->libelle ?? 'N/A',
                    $tarif->poids->libelle ?? 'N/A',
                    $tarif->temp->libelle ?? 'N/A',
                    number_format($tarif->amount, 0, ',', ' '),
                    $tarif->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
