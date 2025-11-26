<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, $requiredPlan = null)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Vérifier que l'utilisateur a une entreprise
        if (!$user->entreprise_id) {
            \Log::warning('CheckSubscription: Utilisateur sans entreprise_id', [
                'user_id' => $user->id,
                'route' => $request->route()->getName()
            ]);
            return redirect()->route('login')
                ->with('error', 'Aucune entreprise associée à votre compte.');
        }

        // Utiliser le nouveau système : obtenir l'abonnement actif de l'entreprise
        // getActiveSubscription charge déjà le pricingPlan avec with('pricingPlan')
        $activeSubscription = SubscriptionPlan::getActiveSubscription($user->entreprise_id);

        \Log::debug('CheckSubscription: Vérification abonnement', [
            'user_id' => $user->id,
            'entreprise_id' => $user->entreprise_id,
            'required_plan' => $requiredPlan,
            'has_subscription' => $activeSubscription ? true : false,
            'subscription_id' => $activeSubscription->id ?? null,
            'subscription_name' => $activeSubscription->name ?? null,
            'pricing_plan_name' => ($activeSubscription && $activeSubscription->pricingPlan) ? $activeSubscription->pricingPlan->name : null,
            'route' => $request->route()->getName()
        ]);

        if (!$activeSubscription) {
            \Log::warning('CheckSubscription: Aucun abonnement actif trouvé', [
                'user_id' => $user->id,
                'entreprise_id' => $user->entreprise_id,
                'route' => $request->route()->getName()
            ]);
            return redirect()->route('subscription.required')
                ->with('error', 'Un abonnement actif est requis pour accéder à cette fonctionnalité.');
        }

        // Vérifier le plan spécifique si requis
        if ($requiredPlan) {
            // Charger le plan tarifaire associé si disponible
            $pricingPlanName = null;
            if ($activeSubscription->pricingPlan) {
                $pricingPlanName = $activeSubscription->pricingPlan->name;
            }

            // Vérifier si c'est un plan Premium (Premium ou Premium Annuel)
            if ($requiredPlan === 'Premium') {
                // Vérifier via le plan tarifaire (plus fiable)
                $isPremium = false;
                if ($pricingPlanName) {
                    $isPremium = in_array($pricingPlanName, ['Premium', 'Premium Annuel']);
                }

                // Fallback : vérifier le nom de l'abonnement (peut contenir le nom de l'entreprise)
                if (!$isPremium) {
                    $subscriptionName = $activeSubscription->name;
                    $isPremium = str_contains($subscriptionName, 'Premium') ||
                                 str_contains($subscriptionName, 'Premium Annuel') ||
                                 in_array($subscriptionName, ['Premium', 'Premium Annuel']);
                }

                // Fallback : vérifier la durée (365 jours = Premium Annuel, 30 jours = Premium)
                if (!$isPremium && $activeSubscription->duration_days) {
                    $isPremium = in_array($activeSubscription->duration_days, [30, 365]);
                }

                if (!$isPremium) {
                    \Log::warning('CheckSubscription: Plan Premium requis mais non trouvé', [
                        'user_id' => $user->id,
                        'entreprise_id' => $user->entreprise_id,
                        'subscription_name' => $activeSubscription->name,
                        'pricing_plan_name' => $pricingPlanName,
                        'duration_days' => $activeSubscription->duration_days,
                        'route' => $request->route()->getName()
                    ]);
                    return redirect()->route('subscription.upgrade')
                        ->with('error', 'Le plan Premium est requis pour accéder à cette fonctionnalité.');
                }

                \Log::debug('CheckSubscription: Plan Premium validé', [
                    'user_id' => $user->id,
                    'entreprise_id' => $user->entreprise_id,
                    'subscription_name' => $activeSubscription->name,
                    'pricing_plan_name' => $pricingPlanName,
                    'route' => $request->route()->getName()
                ]);
            } else {
                // Pour les autres plans, vérifier le nom exact du plan tarifaire ou de l'abonnement
                $planMatches = false;
                if ($pricingPlanName && $pricingPlanName === $requiredPlan) {
                    $planMatches = true;
                } elseif ($activeSubscription->name === $requiredPlan) {
                    $planMatches = true;
                }

                if (!$planMatches) {
                    return redirect()->route('subscription.upgrade')
                        ->with('error', 'Le plan ' . $requiredPlan . ' est requis pour accéder à cette fonctionnalité.');
                }
            }
        }

        return $next($request);
    }
}
