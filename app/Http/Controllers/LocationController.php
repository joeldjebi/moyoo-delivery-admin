<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LivreurLocation;
use App\Models\LivreurLocationStatus;
use App\Models\User;
use App\Models\Livreur;

class LocationController extends Controller
{

    /**
     * Interface de monitoring pour les administrateurs
     */
    public function adminMonitor()
    {
        $menu = 'location-admin';

        // Récupérer les données de l'entreprise
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;

        // Vérification supplémentaire de l'abonnement Premium
        if (!$user->subscription_plan_id ||
            $user->subscription_status !== 'active' ||
            ($user->subscription_expires_at && $user->subscription_expires_at->isPast())) {
            return redirect()->route('subscription.required')
                ->with('error', 'Un abonnement actif est requis pour accéder au moniteur admin.');
        }

        $subscriptionPlan = \App\Models\SubscriptionPlan::find($user->subscription_plan_id);
        if (!$subscriptionPlan || $subscriptionPlan->name !== 'Premium') {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Le plan Premium est requis pour accéder au moniteur admin.');
        }

        // SEULEMENT les livreurs avec des missions actives (livraisons ou ramassages)
        $livreursWithActiveMissions = Livreur::where('entreprise_id', $entrepriseId)
            ->where(function($query) {
                $query->whereHas('colis', function($q) {
                    $q->where('status', 1); // Livraisons en cours
                })->orWhereHas('ramassages', function($q) {
                    $q->where('statut', 'en_cours'); // Ramassages en cours
                });
            })
            ->with(['lastLocation' => function($query) {
                $query->orderBy('timestamp', 'desc');
            }])
            ->with(['locationStatus'])
            ->with(['colis' => function($query) {
                $query->where('status', 1); // Seulement les livraisons en cours
            }])
            ->with(['ramassages' => function($query) {
                $query->where('statut', 'en_cours'); // Seulement les ramassages en cours
            }])
            ->get();

        // Debug: Log des livreurs trouvés
        \Log::info('Livreurs avec missions actives trouvés:', [
            'count' => $livreursWithActiveMissions->count(),
            'livreurs' => $livreursWithActiveMissions->map(function($livreur) {
                return [
                    'id' => $livreur->id,
                    'name' => $livreur->first_name . ' ' . $livreur->last_name,
                    'has_location' => $livreur->lastLocation ? true : false,
                    'location_status' => $livreur->locationStatus ? $livreur->locationStatus->status : 'none',
                    'colis_count' => $livreur->colis->count(),
                    'ramassages_count' => $livreur->ramassages->count()
                ];
            })
        ]);

        // Statistiques basées sur les missions actives
        $stats = [
            'total_livreurs' => Livreur::where('entreprise_id', $entrepriseId)->count(),
            'en_mission' => $livreursWithActiveMissions->count(),
            'en_livraison' => $livreursWithActiveMissions->filter(function($livreur) {
                return $livreur->colis->where('status', 1)->count() > 0;
            })->count(),
            'en_ramassage' => $livreursWithActiveMissions->filter(function($livreur) {
                return $livreur->ramassages->where('statut', 'en_cours')->count() > 0;
            })->count(),
            'hors_mission' => Livreur::where('entreprise_id', $entrepriseId)
                ->whereDoesntHave('colis', function($q) {
                    $q->where('status', 1);
                })
                ->whereDoesntHave('ramassages', function($q) {
                    $q->where('statut', 'en_cours');
                })->count(),
        ];

        // Positions récentes SEULEMENT des livreurs en mission (dernières 30 minutes)
        $recentLocations = LivreurLocation::where('entreprise_id', $entrepriseId)
            ->where('timestamp', '>=', now()->subMinutes(30))
            ->whereHas('livreur', function($query) {
                $query->where(function($q) {
                    $q->whereHas('colis', function($colisQuery) {
                        $colisQuery->where('status', 1);
                    })->orWhereHas('ramassages', function($ramassageQuery) {
                        $ramassageQuery->where('statut', 'en_cours');
                    });
                });
            })
            ->with(['livreur'])
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('location.admin-monitor', compact('menu', 'livreursWithActiveMissions', 'stats', 'recentLocations', 'user'));
    }
}
