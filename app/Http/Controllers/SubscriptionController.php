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

        // Afficher tous les plans actifs (depuis pricing_plans)
        $query = \App\Models\PricingPlan::active()->ordered();

        $data['plans'] = $query->get()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => number_format($plan->price, 0, ',', ' '),
                'currency' => $plan->currency,
                'period' => $plan->period === 'year' ? 'an' : 'mois',
                'description' => $plan->description,
                'features' => $plan->features ?? [],
                'popular' => $plan->is_popular,
                'button_text' => $plan->price == 0 ? 'Passer au ' . $plan->name : 'Choisir ' . $plan->name,
                'button_class' => $plan->is_popular ? 'btn-primary' : 'btn-outline-primary'
            ];
        });
        $data['user'] = Auth::user();

        // Récupérer l'historique des paiements de l'entreprise
        if (auth()->check()) {
            $user = Auth::user();
            $data['subscription_history'] = SubscriptionHistory::where('entreprise_id', $user->entreprise_id)
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
        $plan = \App\Models\PricingPlan::findOrFail($request->plan_id);

        // Vérifier si l'utilisateur a déjà ce plan actif
        if ($user->hasActiveSubscription() && $user->subscription_plan_id == $plan->id) {
            return redirect()->back()->with('error', 'Vous êtes déjà abonné à ce plan.');
        }

        // Vérification spéciale pour le plan Free
        if ($plan->price == 0) {
            return redirect()->back()->with('error', 'Le plan Free n\'est pas disponible pour la souscription. Veuillez choisir un plan Premium.');
        }

        // Pour les plans payants, rediriger vers le paiement
        return redirect()->route('subscriptions.payment', ['plan_id' => $plan->id]);
    }

    /**
     * Créer un nouvel abonnement
     */
    public function createSubscription($pricingPlanId, $entrepriseId, $paymentData = null)
    {
        $pricingPlan = \App\Models\PricingPlan::findOrFail($pricingPlanId);

        // Vérifier s'il y a déjà un abonnement actif du même plan
        $existingActiveSubscription = \App\Models\SubscriptionPlan::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where('pricing_plan_id', $pricingPlanId)
            ->first();

        $isExtension = $existingActiveSubscription !== null;

        if ($isExtension) {
            // Extension d'abonnement existant
            $extensionDays = $pricingPlan->period === 'year' ? 365 : 30;
            $currentExpiration = $existingActiveSubscription->expires_at ?? now();
            $newExpiration = $currentExpiration->addDays($extensionDays);

            // Mettre à jour l'abonnement existant
            $existingActiveSubscription->expires_at = $newExpiration;
            $existingActiveSubscription->save();

            $subscription = $existingActiveSubscription;
            $result = $existingActiveSubscription;
        } else {
            // Nouvel abonnement
            $subscription = new \App\Models\SubscriptionPlan();
            $subscription->name = $pricingPlan->name;
            $subscription->slug = \Str::slug($pricingPlan->name);
            $subscription->entreprise_id = $entrepriseId;
            $subscription->pricing_plan_id = $pricingPlanId;
            $subscription->description = $pricingPlan->description;
            $subscription->price = $pricingPlan->price;
            $subscription->currency = $pricingPlan->currency;
            $subscription->duration_days = $pricingPlan->period === 'year' ? 365 : 30;
            $subscription->features = $pricingPlan->features;
            $subscription->is_active = false; // Sera activé par startSubscription()
            $subscription->save();

            // Démarrer l'abonnement (désactive automatiquement les autres)
            $subscription->startSubscription();
            $result = $subscription;
        }

        // Créer l'historique de paiement
        if ($paymentData) {
            \App\Models\SubscriptionHistory::create([
                'entreprise_id' => $entrepriseId,
                'pricing_plan_id' => $pricingPlanId,
                'plan_name' => $pricingPlan->name,
                'price' => $pricingPlan->price,
                'currency' => $pricingPlan->currency,
                'status' => 'active',
                'payment_method' => $paymentData['payment_method'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'is_extension' => $isExtension,
                'extension_days' => $isExtension ? $subscription->calculateExtensionDays() : null
            ]);
        }

        return [
            'subscription' => $subscription,
            'is_extension' => $isExtension,
            'extended_subscription' => $isExtension ? $result : null
        ];
    }

    /**
     * Activer un abonnement
     */
    public function activate($id)
    {
        try {
            $subscription = \App\Models\SubscriptionPlan::findOrFail($id);
            $user = Auth::user();

            // Vérifier que l'abonnement appartient à l'entreprise de l'utilisateur
            if ($subscription->entreprise_id !== $user->entreprise_id) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            // Activer l'abonnement (désactive automatiquement les autres)
            $subscription->startSubscription();

            return response()->json([
                'success' => true,
                'message' => 'Abonnement activé avec succès. Les autres abonnements ont été désactivés.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Page de paiement
     */
    public function payment($planId)
    {
        $plan = \App\Models\PricingPlan::findOrFail($planId);
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
            'phone_number' => 'required_if:payment_method,mobile_money|nullable|string|regex:/^\+225\d{10}$/',
            'transaction_id' => 'nullable|string',
            'terms' => 'required|accepted'
        ], [
            'plan_id.required' => 'Le plan d\'abonnement est requis.',
            'plan_id.exists' => 'Le plan sélectionné n\'existe pas.',
            'payment_method.required' => 'Veuillez sélectionner une méthode de paiement.',
            'payment_method.in' => 'La méthode de paiement sélectionnée n\'est pas valide.',
            'phone_number.required_if' => 'Le numéro de téléphone est requis pour Mobile Money.',
            'phone_number.regex' => 'Le numéro de téléphone doit être au format +225XXXXXXXXXX (10 chiffres).',
            'terms.required' => 'Vous devez accepter les conditions générales.',
            'terms.accepted' => 'Vous devez accepter les conditions générales et la politique de confidentialité.'
        ]);

        $user = Auth::user();
        $plan = \App\Models\PricingPlan::findOrFail($request->plan_id);

        try {
            // Simuler un paiement réussi (en attendant les APIs de paiement)
            $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);

            // Données de paiement simulées
            $paymentData = [
                'payment_method' => $request->payment_method,
                'transaction_id' => $transactionId,
                'phone_number' => $request->phone_number ?? null,
                'status' => 'success',
                'amount' => $plan->price,
                'currency' => $plan->currency
            ];

            // Créer l'abonnement avec la nouvelle logique d'extension
            $result = $this->createSubscription($plan->id, $user->entreprise_id, $paymentData);

            $isExtension = $result['is_extension'];
            $subscription = $result['subscription'];

            // Log du paiement
            \Log::info('Paiement simulé traité avec succès', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'amount' => $plan->price,
                'payment_method' => $request->payment_method,
                'transaction_id' => $transactionId,
                'is_extension' => $isExtension,
                'subscription_id' => $subscription->id
            ]);

            // Message de succès adapté
            if ($isExtension) {
                $message = 'Paiement effectué avec succès ! Votre abonnement ' . $plan->name . ' a été étendu.';
            } else {
                $message = 'Paiement effectué avec succès ! Votre abonnement ' . $plan->name . ' est maintenant actif.';
            }

            // Rediriger vers la page de succès avec les données
            return redirect()->route('subscriptions.success')
                ->with([
                    'subscription' => $subscription,
                    'plan' => $plan,
                    'transaction_id' => $transactionId,
                    'is_extension' => $isExtension,
                    'success_message' => $message
                ]);

        } catch (\Exception $e) {
            \Log::error('Erreur paiement simulé', [
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
     * Page de succès du paiement
     */
    public function success()
    {
        $data['title'] = 'Paiement Réussi';
        $data['menu'] = 'subscriptions';

        // Récupérer les données de la session
        $data['subscription'] = session('subscription');
        $data['plan'] = session('plan');
        $data['transaction_id'] = session('transaction_id');
        $data['is_extension'] = session('is_extension');
        $data['success_message'] = session('success_message');

        return view('subscriptions.success', $data);
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
                'plan' => $currentSubscription ? $currentSubscription->subscriptionPlan : null,
                'has_active_subscription' => $user->hasActiveSubscription(),
                'subscription_history' => $user->subscriptionHistories()->with('subscriptionPlan')->get()
            ]
        ]);
    }

    /**
     * Assigner un plan à un utilisateur
     */
    private function assignPlanToUser($user, $plan, $isTrial = false, $paymentData = null)
    {
        // Calculer la date d'expiration basée sur duration_days
        $expiresAt = now()->addDays($plan->duration_days);

        // Créer l'historique d'abonnement
        SubscriptionHistory::create([
            'entreprise_id' => $user->entreprise_id,
            'pricing_plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'price' => $plan->price,
            'currency' => $plan->currency,
            'status' => 'active',
            'payment_method' => $paymentData['payment_method'] ?? null,
            'transaction_id' => $paymentData['transaction_id'] ?? null
        ]);

        // Mettre à jour l'utilisateur avec le plan actuel
        $user->update([
            'subscription_plan_id' => $plan->id,
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
