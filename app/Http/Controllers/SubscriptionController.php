<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PricingPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\SubscriptionHistory;

class SubscriptionController extends Controller
{
    /**
     * Afficher la page des abonnements
     */
    public function index()
    {
        $data['title'] = 'Plans d\'Abonnement';
        $data['menu'] = 'subscriptions';

        // Afficher seulement les plans payants pour les utilisateurs connectés
        $query = PricingPlan::active()->ordered();
        if (auth()->check()) {
            $query->where('price', '>', 0);
        }

        $data['plans'] = $query->get();
        $data['user'] = Auth::user();

        // Récupérer l'historique des abonnements de l'utilisateur
        if (auth()->check()) {
            $data['subscription_history'] = SubscriptionHistory::where('user_id', auth()->id())
                ->with('pricingPlan')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('subscriptions.index', $data);
    }

    /**
     * Changer d'abonnement
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:pricing_plans,id'
        ]);

        $user = Auth::user();
        $plan = PricingPlan::findOrFail($request->plan_id);

        // Vérifier si l'utilisateur a déjà ce plan actif
        $currentSubscription = $user->getCurrentSubscription();
        if ($currentSubscription && $currentSubscription->pricing_plan_id == $plan->id && $currentSubscription->isActive()) {
            return redirect()->back()->with('error', 'Vous êtes déjà abonné à ce plan.');
        }

        // Si c'est un plan gratuit, l'assigner directement
        if ($plan->price == 0) {
            $this->assignPlanToUser($user, $plan, false);
            return redirect()->back()->with('success', 'Vous êtes maintenant abonné au plan ' . $plan->name . '.');
        }

        // Pour les plans payants, rediriger vers le paiement
        return redirect()->route('subscriptions.payment', ['plan_id' => $plan->id]);
    }

    /**
     * Page de paiement
     */
    public function payment($planId)
    {
        $plan = PricingPlan::findOrFail($planId);
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
            'plan_id' => 'required|exists:pricing_plans,id',
            'payment_method' => 'required|in:mobile_money,card,bank_transfer',
            'phone_number' => 'required_if:payment_method,mobile_money|string',
            'transaction_id' => 'nullable|string'
        ]);

        $user = Auth::user();
        $plan = PricingPlan::findOrFail($request->plan_id);

        try {
            // Simuler le traitement du paiement
            $paymentResult = $this->processPaymentGateway($request, $plan);

            if ($paymentResult['success']) {
                // Assigner le plan à l'utilisateur
                $this->assignPlanToUser($user, $plan, false, $paymentResult);

                // Log du paiement
                \Log::info('Paiement traité avec succès', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'amount' => $plan->price,
                    'payment_method' => $request->payment_method,
                    'transaction_id' => $paymentResult['transaction_id']
                ]);

                return redirect()->route('subscriptions.index')
                    ->with('success', 'Paiement effectué avec succès ! Votre abonnement ' . $plan->name . ' est maintenant actif.');
            } else {
                return redirect()->back()
                    ->with('error', 'Erreur lors du paiement : ' . $paymentResult['message'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Erreur paiement', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du traitement du paiement.')
                ->withInput();
        }
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
        $currentSubscription = $user->getCurrentSubscription();

        return response()->json([
            'success' => true,
            'data' => [
                'current_subscription' => $currentSubscription,
                'plan' => $currentSubscription ? $currentSubscription->pricingPlan : null,
                'has_active_subscription' => $user->hasActiveSubscription(),
                'subscription_history' => $user->subscriptionHistories()->with('pricingPlan')->get()
            ]
        ]);
    }

    /**
     * Assigner un plan à un utilisateur
     */
    private function assignPlanToUser($user, $plan, $isTrial = false, $paymentData = null)
    {
        // Calculer la date d'expiration
        $expiresAt = $plan->period === 'year'
            ? now()->addYear()
            : now()->addMonth();

        // Créer l'historique d'abonnement
        SubscriptionHistory::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'period' => $plan->period,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
            'is_trial' => $isTrial,
            'payment_method' => $paymentData['payment_method'] ?? null,
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'payment_data' => $paymentData ? json_encode($paymentData) : null
        ]);

        // Mettre à jour l'utilisateur avec le plan actuel
        $user->update([
            'current_pricing_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_expires_at' => $expiresAt
        ]);
    }

    /**
     * Traiter le paiement via la passerelle de paiement
     */
    private function processPaymentGateway($request, $plan)
    {
        // Simulation du traitement de paiement
        // Ici vous intégreriez votre vraie passerelle de paiement (Orange Money, MTN, etc.)

        $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);

        // Simuler différents scénarios
        if ($request->payment_method === 'mobile_money') {
            // Vérifier le numéro de téléphone
            if (empty($request->phone_number)) {
                return [
                    'success' => false,
                    'message' => 'Numéro de téléphone requis pour le paiement mobile money'
                ];
            }
        }

        // Simuler un succès (dans la vraie implémentation, vous feriez l'appel API)
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'payment_method' => $request->payment_method,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'processed_at' => now()
        ];
    }
}
