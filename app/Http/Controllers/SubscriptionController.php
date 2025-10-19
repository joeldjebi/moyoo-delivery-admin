<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionPlan;
use App\Models\User;

class SubscriptionController extends Controller
{
    /**
     * Afficher la page des abonnements
     */
    public function index()
    {
        $data['title'] = 'Plans d\'Abonnement';
        $data['menu'] = 'subscriptions';
        
        $data['plans'] = SubscriptionPlan::active()->ordered()->get();
        $data['user'] = Auth::user();
        
        return view('subscriptions.index', $data);
    }

    /**
     * Changer d'abonnement
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id'
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Vérifier si l'utilisateur peut changer de plan
        if ($user->subscription_plan_id == $plan->id) {
            return redirect()->back()->with('error', 'Vous êtes déjà abonné à ce plan.');
        }

        // Si c'est un plan gratuit, l'assigner directement
        if ($plan->isFree()) {
            $user->assignSubscriptionPlan($plan->id, false); // false = pas de période d'essai
            
            return redirect()->back()->with('success', 'Vous êtes maintenant abonné au plan Free.');
        }

        // Pour les plans payants, rediriger vers le paiement
        return redirect()->route('subscriptions.payment', ['plan_id' => $plan->id]);
    }

    /**
     * Page de paiement
     */
    public function payment($planId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);
        $user = Auth::user();
        
        $data['title'] = 'Paiement - ' . $plan->name;
        $data['menu'] = 'subscriptions';
        $data['plan'] = $plan;
        $data['user'] = $user;
        
        return view('subscriptions.payment', $data);
    }

    /**
     * Traiter le paiement
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|in:mobile_money,card,bank_transfer'
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Ici, vous intégreriez votre système de paiement
        // Pour l'instant, on simule un paiement réussi
        
        // Assigner le plan à l'utilisateur
        $user->assignSubscriptionPlan($plan->id, false);
        
        // Log du paiement (à implémenter selon vos besoins)
        \Log::info('Paiement traité', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_method' => $request->payment_method
        ]);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Paiement effectué avec succès ! Votre abonnement ' . $plan->name . ' est maintenant actif.');
    }

    /**
     * Annuler l'abonnement
     */
    public function cancel()
    {
        $user = Auth::user();
        
        if (!$user->hasActiveSubscription()) {
            return redirect()->back()->with('error', 'Vous n\'avez pas d\'abonnement actif à annuler.');
        }

        $user->update([
            'subscription_status' => 'cancelled'
        ]);

        return redirect()->back()->with('success', 'Votre abonnement a été annulé. Il restera actif jusqu\'à la fin de la période payée.');
    }

    /**
     * API: Obtenir les plans d'abonnement
     */
    public function getPlans()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();
        
        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * API: Obtenir l'abonnement de l'utilisateur
     */
    public function getUserSubscription()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'plan' => $user->subscriptionPlan,
                'status' => $user->subscription_status,
                'is_trial' => $user->is_trial,
                'trial_expires_at' => $user->trial_expires_at,
                'subscription_expires_at' => $user->subscription_expires_at,
                'has_active_subscription' => $user->hasActiveSubscription(),
                'is_on_trial' => $user->isOnTrial()
            ]
        ]);
    }
}
