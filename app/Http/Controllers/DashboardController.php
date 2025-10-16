<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Colis;
use App\Models\Livreur;
use App\Models\Marchand;
use App\Models\Historique_livraison;
use App\Models\Entreprise;
use App\Models\Ramassage;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord avec les vraies données
     */
    public function dashboard()
    {
        try {
            $data['title'] = 'Tableau de Bord';
        $data['menu'] = 'dashboard';

            $user = Auth::user();
            $entrepriseId = $user->entreprise_id ?? 1; // Fallback pour les tests

            // Statistiques des colis
            $data['stats'] = $this->getColisStats($entrepriseId);

            // Statistiques des frais de livraison
            $data['fraisStats'] = $this->getFraisStats($entrepriseId);

            // Statistiques des livreurs
            $data['livreurStats'] = $this->getLivreurStats($entrepriseId);

            // Statistiques des marchands
            $data['marchandStats'] = $this->getMarchandStats($entrepriseId);

            // Graphiques des données
            $data['chartData'] = $this->getChartData($entrepriseId);

            // Dernières activités
            $data['recentActivities'] = $this->getRecentActivities($entrepriseId);

        return view('dashboard', $data);
        } catch (\Exception $e) {
            \Log::error('Erreur Dashboard: ' . $e->getMessage());
            return view('dashboard', [
                'title' => 'Tableau de Bord',
                'menu' => 'dashboard',
                'stats' => ['total' => 0, 'livres' => 0, 'en_cours' => 0, 'en_attente' => 0, 'aujourdhui' => 0, 'cette_semaine' => 0],
                'fraisStats' => ['aujourdhui' => 0, 'cette_semaine' => 0, 'ce_mois' => 0, 'total' => 0],
                'livreurStats' => ['total' => 0, 'actifs' => 0, 'inactifs' => 0],
                'marchandStats' => ['total' => 0, 'actifs' => 0, 'inactifs' => 0],
                'chartData' => ['colis_par_jour' => [], 'colis_par_mois' => [], 'repartition_statut' => []],
                // 'recentActivities' => ['derniers_colis' => [], 'dernieres_livraisons' => []]
            ]);
        }
    }

    /**
     * Obtenir les statistiques des colis
     */
    private function getColisStats($entrepriseId)
    {
        $query = Colis::where('entreprise_id', $entrepriseId);

        // Total des colis
        $totalColis = $query->count();

        // Colis aujourd'hui
        $colisAujourdhui = $query->whereDate('created_at', today())->count();

        // Colis cette semaine
        $colisCetteSemaine = $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        // Colis ce mois
        $colisCeMois = $query->whereMonth('created_at', now()->month)
                           ->whereYear('created_at', now()->year)
                           ->count();

        // Colis livrés
        $colisLivres = $query->where('status', 'livre')->count();

        // Colis en cours
        $colisEnCours = $query->whereIn('status', ['en_cours', 'en_transit'])->count();

        // Colis en attente
        $colisEnAttente = $query->where('status', 'en_attente')->count();

        // Colis annulés
        $colisAnnules = $query->where('status', 'annule')->count();

        return [
            'total' => $totalColis,
            'aujourdhui' => $colisAujourdhui,
            'cette_semaine' => $colisCetteSemaine,
            'ce_mois' => $colisCeMois,
            'livres' => $colisLivres,
            'en_cours' => $colisEnCours,
            'en_attente' => $colisEnAttente,
            'annules' => $colisAnnules
        ];
    }

    /**
     * Obtenir les statistiques des frais de livraison
     */
    private function getFraisStats($entrepriseId)
    {
        $query = Historique_livraison::whereHas('colis', function($q) use ($entrepriseId) {
            $q->where('entreprise_id', $entrepriseId);
        });

        // Total des frais aujourd'hui
        $fraisAujourdhui = $query->whereDate('created_at', today())
                                ->sum('montant_de_la_livraison');

        // Total des frais cette semaine
        $fraisCetteSemaine = $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->sum('montant_de_la_livraison');

        // Total des frais ce mois
        $fraisCeMois = $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->sum('montant_de_la_livraison');

        // Total des frais
        $totalFrais = $query->sum('montant_de_la_livraison');

        return [
            'aujourdhui' => $fraisAujourdhui,
            'cette_semaine' => $fraisCetteSemaine,
            'ce_mois' => $fraisCeMois,
            'total' => $totalFrais
        ];
    }

    /**
     * Obtenir les statistiques des livreurs
     */
    private function getLivreurStats($entrepriseId)
    {
        $query = Livreur::where('entreprise_id', $entrepriseId);

        // Total des livreurs
        $totalLivreurs = $query->count();

        // Livreurs actifs
        $livreursActifs = $query->where('status', 'actif')->count();

        // Livreurs inactifs
        $livreursInactifs = $query->where('status', 'inactif')->count();

        return [
            'total' => $totalLivreurs,
            'actifs' => $livreursActifs,
            'inactifs' => $livreursInactifs
        ];
    }

    /**
     * Obtenir les statistiques des marchands
     */
    private function getMarchandStats($entrepriseId)
    {
        $query = Marchand::where('entreprise_id', $entrepriseId);

        // Total des marchands
        $totalMarchands = $query->count();

        // Marchands actifs
        $marchandsActifs = $query->where('status', 'actif')->count();

        // Marchands inactifs
        $marchandsInactifs = $query->where('status', 'inactif')->count();

        return [
            'total' => $totalMarchands,
            'actifs' => $marchandsActifs,
            'inactifs' => $marchandsInactifs
        ];
    }

    /**
     * Obtenir les données pour les graphiques
     */
    private function getChartData($entrepriseId, $month = null)
    {
        // Si un mois est spécifié, afficher les données de ce mois
        if ($month) {
            $year = now()->year;
            $startDate = now()->setMonth($month)->setDay(1)->startOfMonth();
            $endDate = now()->setMonth($month)->endOfMonth();

            // Données par jour du mois sélectionné
            $shipmentData = [];
            $deliveryData = [];
            $labels = [];

            $daysInMonth = $startDate->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $startDate->copy()->setDay($day);
                $dateStr = $date->format('d M');
                $labels[] = $dateStr;

                // Nombre de colis créés (expéditions)
                $shipmentCount = Colis::where('entreprise_id', $entrepriseId)
                                     ->whereDate('created_at', $date)
                                     ->count();
                $shipmentData[] = $shipmentCount;

                // Nombre de livraisons effectuées (colis avec statut "livré" = 2)
                $deliveryCount = Colis::where('entreprise_id', $entrepriseId)
                                     ->where('status', 2) // STATUS_LIVRE
                                     ->whereDate('updated_at', $date)
                                     ->count();
                $deliveryData[] = $deliveryCount;
            }
        } else {
            // Données des 10 derniers jours pour le graphique d'expédition
            $shipmentData = [];
            $deliveryData = [];
            $labels = [];

            for ($i = 9; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateStr = $date->format('d M');
                $labels[] = $dateStr;

                // Nombre de colis créés (expéditions)
                $shipmentCount = Colis::where('entreprise_id', $entrepriseId)
                                     ->whereDate('created_at', $date)
                                     ->count();
                $shipmentData[] = $shipmentCount;

                // Nombre de livraisons effectuées (colis avec statut "livré" = 2)
                $deliveryCount = Colis::where('entreprise_id', $entrepriseId)
                                     ->where('status', 2) // STATUS_LIVRE
                                     ->whereDate('updated_at', $date)
                                     ->count();
                $deliveryData[] = $deliveryCount;
            }
        }

        // Données des 12 derniers mois
        $colisParMois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Colis::where('entreprise_id', $entrepriseId)
                         ->whereMonth('created_at', $date->month)
                         ->whereYear('created_at', $date->year)
                         ->count();
            $colisParMois[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }

        // Répartition par statut
        $repartitionStatut = Colis::where('entreprise_id', $entrepriseId)
                                 ->select('status', DB::raw('count(*) as count'))
                                 ->groupBy('status')
                                 ->get()
                                 ->pluck('count', 'status')
                                 ->toArray();

        return [
            'shipment_labels' => $labels,
            'shipment_data' => $shipmentData,
            'delivery_data' => $deliveryData,
            'colis_par_mois' => $colisParMois,
            'repartition_statut' => $repartitionStatut
        ];
    }

    /**
     * Obtenir les activités récentes
     */
    private function getRecentActivities($entrepriseId)
    {
        try {
            // Derniers colis créés
            $derniersColis = Colis::where('entreprise_id', $entrepriseId)
                                 ->with(['livreur', 'commune_zone'])
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();

            // Dernières livraisons (depuis historique_livraisons)
            // $dernieresLivraisons = Historique_livraison::where('entreprise_id', $entrepriseId)
            //                                           ->with(['colis', 'livreur', 'colis.commune_zone.marchand'])
            //                                           ->orderBy('created_at', 'desc')
            //                                           ->limit(5)
            //                                           ->get();

            // Dernières boutiques avec des colis créés
            $dernieresBoutiques = \App\Models\Boutique::where('entreprise_id', $entrepriseId)
                                                    ->with(['marchand'])
                                                    ->orderBy('created_at', 'desc')
                                                    ->limit(5)
                                                    ->get();

            // Calcul des performances de livraison
            $performanceData = $this->getDeliveryPerformance($entrepriseId);


            // Données des commandes par communes
            // $communesData = $this->getCommuneOrders($entrepriseId);

            // Données des colis en cours de livraison
            $perPage = request('per_page', 5);
            $colisEnCoursData = $this->getColisEnCours($entrepriseId, $perPage);

            // Données des ramassages
            $perPageRamassages = request('per_page_ramassages', 5);
            $ramassagesData = $this->getRamassagesData($entrepriseId, $perPageRamassages);

            return [
                'derniers_colis' => $derniersColis,
                // 'dernieres_livraisons' => $dernieresLivraisons,
                'dernieres_boutiques' => $dernieresBoutiques,
                'performance_data' => $performanceData,
                // 'communes_data' => $communesData,
                'colis_en_cours_data' => $colisEnCoursData,
                'ramassages_data' => $ramassagesData
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur getRecentActivities: ' . $e->getMessage());

            // Retourner des valeurs par défaut en cas d'erreur
            return [
                'derniers_colis' => collect(),
                'dernieres_livraisons' => collect(),
                'dernieres_boutiques' => collect(),
                'performance_data' => [],
                'exceptions_data' => [],
                'communes_data' => [],
                'colis_en_cours_data' => [],
                'ramassages_data' => [
                    'derniers_ramassages' => collect(),
                    'statistiques' => []
                ]
            ];
        }
    }

    /**
     * Calculer les performances de livraison
     */
    private function getDeliveryPerformance($entrepriseId)
    {
        // Statistiques actuelles
        $totalColis = Colis::where('entreprise_id', $entrepriseId)->count();
        $colisEnTransit = Colis::where('entreprise_id', $entrepriseId)->where('status', 1)->count(); // En transit
        $colisEnLivraison = Colis::where('entreprise_id', $entrepriseId)->where('status', 2)->count(); // Livré
        $colisEnAttente = Historique_livraison::where('entreprise_id', $entrepriseId)->where('status', 'en_attente')->count();

        // Statistiques du mois précédent pour calculer les variations
        $lastMonth = now()->subMonth();
        $totalColisLastMonth = Colis::where('entreprise_id', $entrepriseId)
                                   ->whereMonth('created_at', $lastMonth->month)
                                   ->whereYear('created_at', $lastMonth->year)
                                   ->count();

        $colisEnTransitLastMonth = Colis::where('entreprise_id', $entrepriseId)
                                       ->where('status', 1)
                                       ->whereMonth('created_at', $lastMonth->month)
                                       ->whereYear('created_at', $lastMonth->year)
                                       ->count();

        $colisEnLivraisonLastMonth = Colis::where('entreprise_id', $entrepriseId)
                                         ->where('status', 2)
                                         ->whereMonth('created_at', $lastMonth->month)
                                         ->whereYear('created_at', $lastMonth->year)
                                         ->count();

        // Calcul des pourcentages de variation
        $transitVariation = $totalColisLastMonth > 0 ?
            round((($colisEnTransit - $colisEnTransitLastMonth) / $totalColisLastMonth) * 100, 1) : 0;

        $deliveryVariation = $totalColisLastMonth > 0 ?
            round((($colisEnLivraison - $colisEnLivraisonLastMonth) / $totalColisLastMonth) * 100, 1) : 0;

        // Taux de succès de livraison
        $successRate = $totalColis > 0 ? round(($colisEnLivraison / $totalColis) * 100, 1) : 0;

        // Temps moyen de livraison (en jours)
        $avgDeliveryTime = $this->calculateAverageDeliveryTime($entrepriseId);

        // Satisfaction client (simulé basé sur le taux de succès)
        $customerSatisfaction = min(5, max(1, ($successRate / 100) * 5));

        return [
            'packages_in_transit' => [
                'count' => $colisEnTransit,
                'variation' => $transitVariation,
                'variation_type' => $transitVariation >= 0 ? 'success' : 'danger'
            ],
            'packages_out_for_delivery' => [
                'count' => $colisEnAttente,
                'variation' => 4.3, // Simulé
                'variation_type' => 'success'
            ],
            'packages_delivered' => [
                'count' => $colisEnLivraison,
                'variation' => $deliveryVariation,
                'variation_type' => $deliveryVariation >= 0 ? 'success' : 'danger'
            ],
            'delivery_success_rate' => [
                'rate' => $successRate,
                'variation' => 35.6, // Simulé
                'variation_type' => 'success'
            ],
            'average_delivery_time' => [
                'days' => $avgDeliveryTime,
                'variation' => -2.15, // Simulé
                'variation_type' => 'danger'
            ],
            'customer_satisfaction' => [
                'rating' => $customerSatisfaction,
                'variation' => 5.7, // Simulé
                'variation_type' => 'success'
            ]
        ];
    }

    /**
     * Calculer le temps moyen de livraison
     */
    private function calculateAverageDeliveryTime($entrepriseId)
    {
        $deliveredColis = Colis::where('entreprise_id', $entrepriseId)
                              ->where('status', 2)
                              ->whereNotNull('created_at')
                              ->whereNotNull('updated_at')
                              ->get();

        if ($deliveredColis->isEmpty()) {
            return 2.5; // Valeur par défaut
        }

        $totalDays = 0;
        foreach ($deliveredColis as $colis) {
            $days = $colis->created_at->diffInDays($colis->updated_at);
            $totalDays += $days;
        }

        return round($totalDays / $deliveredColis->count(), 1);
    }


    /**
     * Récupérer les commandes par communes
     */
    // private function getCommuneOrders($entrepriseId)
    // {
    //     // Récupérer les colis groupés par commune avec leurs statuts
    //     $nouvellesCommandes = Colis::where('entreprise_id', $entrepriseId)
    //                              ->where('status', 0) // Nouveau
    //                              ->with(['commune_zone.commune'])
    //                              ->orderBy('created_at', 'desc')
    //                              ->limit(4)
    //                              ->get();

    //     $enPreparation = Colis::where('entreprise_id', $entrepriseId)
    //                         ->where('status', 1) // En transit
    //                         ->with(['commune_zone.commune'])
    //                         ->orderBy('created_at', 'desc')
    //                         ->limit(4)
    //                         ->get();

    //     $enCoursLivraison = Historique_livraison::where('entreprise_id', $entrepriseId)
    //                                           ->where('status', 'en_attente')
    //                                           ->with(['colis.commune_zone.commune'])
    //                                           ->orderBy('created_at', 'desc')
    //                                           ->limit(4)
    //                                           ->get();

    //     // Calculer le total des livraisons en cours
    //     $totalLivraisonsEnCours = Historique_livraison::where('entreprise_id', $entrepriseId)
    //                                                  ->where('status', 'en_attente')
    //                                                  ->count();

    //     return [
    //         'total_livraisons_en_cours' => $totalLivraisonsEnCours,
    //         'nouvelles_commandes' => $nouvellesCommandes,
    //         'en_preparation' => $enPreparation,
    //         'en_cours_livraison' => $enCoursLivraison
    //     ];
    // }

    /**
     * Récupérer les colis en cours de livraison
     */
    private function getColisEnCours($entrepriseId, $perPage = 10)
    {
        // Récupérer les colis en cours de livraison depuis historique_livraisons
        $colisEnCours = Historique_livraison::where('entreprise_id', $entrepriseId)
                                          ->where('status', 'en_attente')
                                          ->with([
                                              'colis.commune_zone.commune',
                                              'colis.commune_zone.marchand',
                                              'livreur'
                                          ])
                                          ->orderBy('created_at', 'desc')
                                          ->paginate($perPage);

        return $colisEnCours;
    }

    /**
     * API pour obtenir les données du dashboard (AJAX)
     */
    public function getDashboardData(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;

        $data = [
            'stats' => $this->getColisStats($entrepriseId),
            'fraisStats' => $this->getFraisStats($entrepriseId),
            'livreurStats' => $this->getLivreurStats($entrepriseId),
            'marchandStats' => $this->getMarchandStats($entrepriseId),
            'chartData' => $this->getChartData($entrepriseId)
        ];

        return response()->json($data);
    }

    /**
     * API pour obtenir les colis en cours paginés (AJAX)
     */
    public function getColisEnCoursPaginated(Request $request)
    {
        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id ?? 1;

            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $colisEnCours = $this->getColisEnCours($entrepriseId, $perPage);

            // Retourner seulement les données nécessaires pour la pagination
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $colisEnCours->items(),
                    'pagination' => [
                        'current_page' => $colisEnCours->currentPage(),
                        'last_page' => $colisEnCours->lastPage(),
                        'per_page' => $colisEnCours->perPage(),
                        'total' => $colisEnCours->total(),
                        'from' => $colisEnCours->firstItem(),
                        'to' => $colisEnCours->lastItem(),
                        'has_more_pages' => $colisEnCours->hasMorePages(),
                        'has_pages' => $colisEnCours->hasPages()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getColisEnCoursPaginated: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des données'
            ], 500);
        }
    }

    /**
     * API pour obtenir les ramassages paginés (AJAX)
     */
    public function getRamassagesPaginated(Request $request)
    {
        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id ?? 1;

            // Si l'utilisateur n'a pas d'entreprise_id, essayer de trouver son entreprise
            if ((!$entrepriseId || $entrepriseId == 1) && $user->id) {
                $entreprise = Entreprise::where('created_by', $user->id)->first();
                $entrepriseId = $entreprise ? $entreprise->id : 1;
            }

            $perPage = $request->get('per_page', 5);
            $page = $request->get('page', 1);

            // Récupérer directement les ramassages paginés
            $ramassages = Ramassage::where('entreprise_id', $entrepriseId)
                ->with(['marchand', 'boutique', 'planifications.livreur'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Retourner seulement les données nécessaires pour la pagination
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $ramassages->items(),
                    'pagination' => [
                        'current_page' => $ramassages->currentPage(),
                        'last_page' => $ramassages->lastPage(),
                        'per_page' => $ramassages->perPage(),
                        'total' => $ramassages->total(),
                        'from' => $ramassages->firstItem(),
                        'to' => $ramassages->lastItem(),
                        'has_more_pages' => $ramassages->hasMorePages(),
                        'has_pages' => $ramassages->hasPages()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur getRamassagesPaginated: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des données'
            ], 500);
        }
    }

    /**
     * Récupérer les données des ramassages pour le dashboard
     */
    private function getRamassagesData($entrepriseId, $perPage = 5)
    {
        // Derniers ramassages avec pagination
        $derniersRamassages = Ramassage::where('entreprise_id', $entrepriseId)
            ->with(['marchand', 'boutique', 'planifications.livreur'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Statistiques des ramassages
        $totalRamassages = Ramassage::where('entreprise_id', $entrepriseId)->count();
        $ramassagesDemande = Ramassage::where('entreprise_id', $entrepriseId)->where('statut', 'demande')->count();
        $ramassagesPlanifies = Ramassage::where('entreprise_id', $entrepriseId)->where('statut', 'planifie')->count();
        $ramassagesEnCours = Ramassage::where('entreprise_id', $entrepriseId)->where('statut', 'en_cours')->count();
        $ramassagesTermines = Ramassage::where('entreprise_id', $entrepriseId)->where('statut', 'termine')->count();

        // Ramassages du mois en cours
        $ramassagesCeMois = Ramassage::where('entreprise_id', $entrepriseId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Ramassages du mois précédent pour calculer la variation
        $ramassagesMoisPrecedent = Ramassage::where('entreprise_id', $entrepriseId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $variationRamassages = $ramassagesMoisPrecedent > 0 ?
            round((($ramassagesCeMois - $ramassagesMoisPrecedent) / $ramassagesMoisPrecedent) * 100, 1) : 0;

        return [
            'derniers_ramassages' => $derniersRamassages,
            'statistiques' => [
                'total' => $totalRamassages,
                'demande' => $ramassagesDemande,
                'planifies' => $ramassagesPlanifies,
                'en_cours' => $ramassagesEnCours,
                'termines' => $ramassagesTermines,
                'ce_mois' => $ramassagesCeMois,
                'variation' => $variationRamassages,
                'variation_type' => $variationRamassages >= 0 ? 'success' : 'danger'
            ]
        ];
    }
}
