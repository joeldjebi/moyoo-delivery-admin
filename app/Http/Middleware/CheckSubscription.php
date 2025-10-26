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

        // Vérifier si l'utilisateur a un abonnement actif
        if (!$user->subscription_plan_id ||
            $user->subscription_status !== 'active' ||
            ($user->subscription_expires_at && $user->subscription_expires_at->isPast())) {
            return redirect()->route('subscription.required')
                ->with('error', 'Un abonnement actif est requis pour accéder à cette fonctionnalité.');
        }

        // Vérifier le plan spécifique si requis
        if ($requiredPlan) {
            $subscriptionPlan = SubscriptionPlan::find($user->subscription_plan_id);
            if (!$subscriptionPlan) {
                return redirect()->route('subscription.upgrade')
                    ->with('error', 'Un plan d\'abonnement est requis pour accéder à cette fonctionnalité.');
            }

            // Vérifier si c'est un plan Premium (Premium ou Premium Annuel)
            if ($requiredPlan === 'Premium') {
                if (!in_array($subscriptionPlan->name, ['Premium', 'Premium Annuel'])) {
                    return redirect()->route('subscription.upgrade')
                        ->with('error', 'Le plan Premium est requis pour accéder à cette fonctionnalité.');
                }
            } elseif ($subscriptionPlan->name !== $requiredPlan) {
                return redirect()->route('subscription.upgrade')
                    ->with('error', 'Le plan ' . $requiredPlan . ' est requis pour accéder à cette fonctionnalité.');
            }
        }

        return $next($request);
    }
}
