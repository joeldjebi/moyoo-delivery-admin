<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Colis;
use App\Models\Historique_livraison;
use App\Models\Ramassage;
use App\Models\FraisLivraison;
use App\Models\Entreprise;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        // Si l'utilisateur n'a pas d'entreprise_id, essayer de trouver son entreprise
        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        // Statistiques générales
        $stats = $this->getGeneralStats($entrepriseId);

        // Données pour les graphiques
        $chartData = $this->getChartData($entrepriseId);

        $data = [
            'title' => 'Rapports et Statistiques',
            'menu' => 'rapports',
            'stats' => $stats,
            'chartData' => $chartData
        ];

        return view('rapports.index', $data);
    }

    public function show($type, Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $data = [
            'title' => 'Rapport ' . ucfirst($type),
            'menu' => 'rapports',
            'type' => $type,
            'entrepriseId' => $entrepriseId
        ];

        switch ($type) {
            case 'livraisons':
                $data['livraisons'] = $this->getLivraisonsReport($entrepriseId, $request);
                $data['livreurs'] = \App\Models\Livreur::where('entreprise_id', $entrepriseId)->get();
                break;
            case 'colis':
                $data['colis'] = $this->getColisReport($entrepriseId, $request);
                $data['zones'] = \App\Models\Zone::where('entreprise_id', $entrepriseId)->get();
                break;
            case 'ramassages':
                $data['ramassages'] = $this->getRamassagesReport($entrepriseId, $request);
                $data['marchands'] = \App\Models\Marchand::where('entreprise_id', $entrepriseId)->get();
                break;
            case 'frais':
                $data['frais'] = $this->getFraisReport($entrepriseId, $request);
                $data['fraisStats'] = $this->getFraisStatistics($entrepriseId, $request);
                $data['livreurs'] = \App\Models\Livreur::where('entreprise_id', $entrepriseId)->get();
                break;
            default:
                return redirect()->route('rapports.index')->with('error', 'Type de rapport non reconnu');
        }

        return view('rapports.show', $data);
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
        $type = $request->get('type', 'all');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');

        $results = [];

        if ($type === 'all' || $type === 'colis') {
            $results['colis'] = $this->searchColis($entrepriseId, $query, $dateDebut, $dateFin);
        }

        if ($type === 'all' || $type === 'livraisons') {
            $results['livraisons'] = $this->searchLivraisons($entrepriseId, $query, $dateDebut, $dateFin);
        }

        if ($type === 'all' || $type === 'ramassages') {
            $results['ramassages'] = $this->searchRamassages($entrepriseId, $query, $dateDebut, $dateFin);
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    private function getGeneralStats($entrepriseId)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'colis' => [
                'total' => Colis::whereHas('zone', function($q) use ($entrepriseId) {
                    $q->where('entreprise_id', $entrepriseId);
                })->count(),
                'aujourd_hui' => Colis::whereHas('zone', function($q) use ($entrepriseId) {
                    $q->where('entreprise_id', $entrepriseId);
                })->whereDate('created_at', $today)->count(),
                'ce_mois' => Colis::whereHas('zone', function($q) use ($entrepriseId) {
                    $q->where('entreprise_id', $entrepriseId);
                })->where('created_at', '>=', $thisMonth)->count(),
                'mois_precedent' => Colis::whereHas('zone', function($q) use ($entrepriseId) {
                    $q->where('entreprise_id', $entrepriseId);
                })->whereBetween('created_at', [$lastMonth, $thisMonth])->count()
            ],
            'livraisons' => [
                'total' => Historique_livraison::where('entreprise_id', $entrepriseId)->count(),
                'livrees' => Historique_livraison::where('entreprise_id', $entrepriseId)->where('status', 'livre')->count(),
                'en_cours' => Historique_livraison::where('entreprise_id', $entrepriseId)->where('status', 'en_attente')->count(),
                'taux_reussite' => $this->calculateSuccessRate($entrepriseId)
            ],
            'ramassages' => [
                'total' => Ramassage::where('entreprise_id', $entrepriseId)->count(),
                'termines' => Ramassage::where('entreprise_id', $entrepriseId)->where('statut', 'termine')->count(),
                'en_cours' => Ramassage::where('entreprise_id', $entrepriseId)->whereIn('statut', ['planifie', 'en_cours'])->count()
            ]
        ];
    }

    private function getChartData($entrepriseId)
    {
        $last12Months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $last12Months[] = [
                'month' => $date->format('M Y'),
                'colis' => Colis::whereHas('zone', function($q) use ($entrepriseId) {
                    $q->where('entreprise_id', $entrepriseId);
                })->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month)->count(),
                'livraisons' => Historique_livraison::where('entreprise_id', $entrepriseId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count()
            ];
        }

        return $last12Months;
    }


    public function showLivraison($id)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id ?? 1;

        if (!$entrepriseId || $entrepriseId == 1) {
            $entreprise = Entreprise::where('created_by', $user->id)->first();
            $entrepriseId = $entreprise ? $entreprise->id : 1;
        }

        $livraison = Historique_livraison::where('id', $id)
            ->where('entreprise_id', $entrepriseId)
            ->with([
                'colis.zone',
                'colis.packageColis.marchand',
                'colis.packageColis.boutique',
                'livreur',
                'livraison',
                'packageColis.marchand',
                'packageColis.boutique'
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'livraison' => $livraison
        ]);
    }

    private function getLivraisonsReport($entrepriseId, $request = null)
    {
        $query = Historique_livraison::where('entreprise_id', $entrepriseId)
            ->with(['colis.zone', 'livreur', 'packageColis', 'livraison']);

        if ($request) {
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->whereHas('colis', function($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                          ->orWhere('nom_client', 'like', "%{$search}%");
                    })->orWhereHas('livreur', function($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('livreur_id')) {
                $query->where('livreur_id', $request->get('livreur_id'));
            }

            if ($request->filled('date_debut') && $request->filled('date_fin')) {
                $query->whereBetween('created_at', [
                    $request->get('date_debut') . ' 00:00:00',
                    $request->get('date_fin') . ' 23:59:59'
                ]);
            }
        }

        $perPage = $request ? $request->get('per_page', 5) : 5;
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    private function getColisReport($entrepriseId, $request = null)
    {
        $query = Colis::whereHas('zone', function($q) use ($entrepriseId) {
            $q->where('entreprise_id', $entrepriseId);
        })->with(['zone']);

        if ($request) {
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('nom_client', 'like', "%{$search}%")
                      ->orWhere('telephone_client', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('zone_id')) {
                $query->where('zone_id', $request->get('zone_id'));
            }

            if ($request->filled('date_debut') && $request->filled('date_fin')) {
                $query->whereBetween('created_at', [
                    $request->get('date_debut') . ' 00:00:00',
                    $request->get('date_fin') . ' 23:59:59'
                ]);
            }
        }

        $perPage = $request ? $request->get('per_page', 5) : 5;
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    private function getRamassagesReport($entrepriseId, $request = null)
    {
        $query = Ramassage::where('entreprise_id', $entrepriseId)
            ->with(['marchand', 'boutique']);

        if ($request) {
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('code_ramassage', 'like', "%{$search}%")
                      ->orWhereHas('marchand', function($q) use ($search) {
                          $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('boutique', function($q) use ($search) {
                          $q->where('nom', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->get('statut'));
            }

            if ($request->filled('marchand_id')) {
                $query->where('marchand_id', $request->get('marchand_id'));
            }

            if ($request->filled('date_debut') && $request->filled('date_fin')) {
                $query->whereBetween('created_at', [
                    $request->get('date_debut') . ' 00:00:00',
                    $request->get('date_fin') . ' 23:59:59'
                ]);
            }
        }

        $perPage = $request ? $request->get('per_page', 5) : 5;
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    private function getFraisReport($entrepriseId, $request = null)
    {
        $query = Historique_livraison::where('entreprise_id', $entrepriseId)
            ->with(['colis', 'livreur']); // Toutes les livraisons pour le rapport des frais

        if ($request) {
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->whereHas('colis', function($colisQuery) use ($search) {
                        $colisQuery->where('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('livreur', function($livreurQuery) use ($search) {
                        $livreurQuery->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            }

            if ($request->filled('livreur_id')) {
                $query->where('livreur_id', $request->get('livreur_id'));
            }

            if ($request->filled('date_debut') && $request->filled('date_fin')) {
                $query->whereBetween('created_at', [
                    $request->get('date_debut') . ' 00:00:00',
                    $request->get('date_fin') . ' 23:59:59'
                ]);
            }
        }

        $perPage = $request ? $request->get('per_page', 5) : 5;
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    // Ajouter une méthode pour les statistiques des frais
    private function getFraisStatistics($entrepriseId, $request = null)
    {
        $query = Historique_livraison::where('entreprise_id', $entrepriseId);

        if ($request && $request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('created_at', [
                $request->get('date_debut') . ' 00:00:00',
                $request->get('date_fin') . ' 23:59:59'
            ]);
        }

        // Requête pour toutes les livraisons
        $allQuery = clone $query;

        // Requête pour les livraisons avec statut "livré"
        $livreQuery = clone $query;
        $livreQuery->where('status', 'livre');

        // Requête pour les livraisons en attente ou en cours
        $enAttenteQuery = clone $query;
        $enAttenteQuery->whereIn('status', ['en_attente', 'en_cours']);

        return [
            // Statistiques générales
            'total_livraisons' => $allQuery->count(),
            'total_livraisons_livrees' => $livreQuery->count(),
            'total_livraisons_en_attente' => $enAttenteQuery->count(),

            // Montants totaux (toutes livraisons)
            'total_frais_livraison' => $allQuery->sum('montant_de_la_livraison'),
            'total_encaissement' => $allQuery->sum('montant_a_encaisse'),
            'total_vente' => $allQuery->sum('prix_de_vente'),

            // Montants pour les livraisons livrées uniquement
            'total_frais_livraisons_livrees' => $livreQuery->sum('montant_de_la_livraison'),
            'total_encaissement_livrees' => $livreQuery->sum('montant_a_encaisse'),
            'total_vente_livrees' => $livreQuery->sum('prix_de_vente'),

            // Montants pour les livraisons en attente/en cours
            'total_frais_livraisons_en_attente' => $enAttenteQuery->sum('montant_de_la_livraison'),
            'total_encaissement_en_attente' => $enAttenteQuery->sum('montant_a_encaisse'),
            'total_vente_en_attente' => $enAttenteQuery->sum('prix_de_vente'),

            // Statistiques par livreur
            'frais_par_livreur' => $allQuery->selectRaw('livreur_id, COUNT(*) as nb_livraisons, SUM(montant_de_la_livraison) as total_frais')
                ->with('livreur')
                ->groupBy('livreur_id')
                ->get()
        ];
    }

    private function searchColis($entrepriseId, $query, $dateDebut, $dateFin)
    {
        $q = Colis::whereHas('zone', function($q) use ($entrepriseId) {
            $q->where('entreprise_id', $entrepriseId);
        })->with(['zone']);

        if ($query) {
            $q->where(function($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                  ->orWhere('nom_client', 'like', "%{$query}%")
                  ->orWhere('telephone_client', 'like', "%{$query}%");
            });
        }

        if ($dateDebut && $dateFin) {
            $q->whereBetween('created_at', [$dateDebut, $dateFin]);
        }

        return $q->orderBy('created_at', 'desc')->limit(10)->get();
    }

    private function searchLivraisons($entrepriseId, $query, $dateDebut, $dateFin)
    {
        $q = Historique_livraison::where('entreprise_id', $entrepriseId)
            ->with(['colis.zone', 'livreur']);

        if ($query) {
            $q->whereHas('colis', function($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                  ->orWhere('nom_client', 'like', "%{$query}%");
            });
        }

        if ($dateDebut && $dateFin) {
            $q->whereBetween('created_at', [$dateDebut, $dateFin]);
        }

        return $q->orderBy('created_at', 'desc')->limit(10)->get();
    }

    private function searchRamassages($entrepriseId, $query, $dateDebut, $dateFin)
    {
        $q = Ramassage::where('entreprise_id', $entrepriseId)
            ->with(['marchand', 'boutique']);

        if ($query) {
            $q->where(function($q) use ($query) {
                $q->where('code_ramassage', 'like', "%{$query}%")
                  ->orWhereHas('marchand', function($q) use ($query) {
                      $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                  });
            });
        }

        if ($dateDebut && $dateFin) {
            $q->whereBetween('created_at', [$dateDebut, $dateFin]);
        }

        return $q->orderBy('created_at', 'desc')->limit(10)->get();
    }

    private function calculateSuccessRate($entrepriseId)
    {
        $total = Historique_livraison::where('entreprise_id', $entrepriseId)->count();
        $livrees = Historique_livraison::where('entreprise_id', $entrepriseId)->where('status', 'livre')->count();

        return $total > 0 ? round(($livrees / $total) * 100, 2) : 0;
    }
}
