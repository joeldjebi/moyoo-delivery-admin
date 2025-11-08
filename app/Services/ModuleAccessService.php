<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\PricingPlan;
use App\Models\Module;
use Illuminate\Support\Facades\Log;

class ModuleAccessService
{
    /**
     * Vérifier si l'entreprise a accès à un module
     */
    public function hasAccess($entrepriseId, $moduleSlug)
    {
        // Le dashboard est toujours accessible
        if ($moduleSlug === 'dashboard') {
            return true;
        }

        try {
            $subscription = SubscriptionPlan::getActiveSubscription($entrepriseId);

            if (!$subscription || !$subscription->pricing_plan_id) {
                Log::debug('ModuleAccessService: Pas d\'abonnement actif', [
                    'entreprise_id' => $entrepriseId,
                    'module_slug' => $moduleSlug
                ]);
                return false;
            }

            $pricingPlan = PricingPlan::with('modules')->find($subscription->pricing_plan_id);

            if (!$pricingPlan) {
                Log::debug('ModuleAccessService: Pricing plan non trouvé', [
                    'entreprise_id' => $entrepriseId,
                    'pricing_plan_id' => $subscription->pricing_plan_id,
                    'module_slug' => $moduleSlug
                ]);
                return false;
            }

            $hasModule = $pricingPlan->modules()
                ->where('slug', $moduleSlug)
                ->wherePivot('is_enabled', true)
                ->exists();

            Log::debug('ModuleAccessService: Vérification accès module', [
                'entreprise_id' => $entrepriseId,
                'module_slug' => $moduleSlug,
                'has_access' => $hasModule
            ]);

            return $hasModule;
        } catch (\Exception $e) {
            Log::error('ModuleAccessService: Erreur lors de la vérification', [
                'entreprise_id' => $entrepriseId,
                'module_slug' => $moduleSlug,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtenir les limites d'un module pour une entreprise
     */
    public function getModuleLimit($entrepriseId, $moduleSlug, $limitKey)
    {
        try {
            $subscription = SubscriptionPlan::getActiveSubscription($entrepriseId);

            if (!$subscription || !$subscription->pricing_plan_id) {
                return null;
            }

            $pricingPlan = PricingPlan::with('modules')->find($subscription->pricing_plan_id);

            if (!$pricingPlan) {
                return null;
            }

            $module = $pricingPlan->modules()
                ->where('slug', $moduleSlug)
                ->wherePivot('is_enabled', true)
                ->first();

            if (!$module) {
                return null;
            }

            $limits = json_decode($module->pivot->limits, true);

            return $limits[$limitKey] ?? null;
        } catch (\Exception $e) {
            Log::error('ModuleAccessService: Erreur lors de la récupération des limites', [
                'entreprise_id' => $entrepriseId,
                'module_slug' => $moduleSlug,
                'limit_key' => $limitKey,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Vérifier si l'entreprise peut créer des colis
     */
    public function canCreateColis($entrepriseId)
    {
        if (!$this->hasAccess($entrepriseId, 'colis_management')) {
            return false;
        }

        $maxColis = $this->getModuleLimit($entrepriseId, 'colis_management', 'max_per_month');

        if ($maxColis === null) {
            return true; // Illimité
        }

        $currentMonthColis = \App\Models\Colis::whereHas('packageColis', function($query) use ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count();

        return $currentMonthColis < $maxColis;
    }

    /**
     * Vérifier si l'entreprise peut créer des livreurs
     */
    public function canCreateLivreur($entrepriseId)
    {
        if (!$this->hasAccess($entrepriseId, 'livreur_management')) {
            return false;
        }

        $maxLivreurs = $this->getModuleLimit($entrepriseId, 'livreur_management', 'max_livreurs');

        if ($maxLivreurs === null) {
            return true; // Illimité
        }

        $currentLivreurs = \App\Models\Livreur::where('entreprise_id', $entrepriseId)
            ->where('status', 'actif')
            ->count();

        return $currentLivreurs < $maxLivreurs;
    }

    /**
     * Vérifier si l'entreprise peut créer des marchands
     */
    public function canCreateMarchand($entrepriseId)
    {
        if (!$this->hasAccess($entrepriseId, 'marchand_management')) {
            return false;
        }

        $maxMarchands = $this->getModuleLimit($entrepriseId, 'marchand_management', 'max_marchands');

        if ($maxMarchands === null) {
            return true; // Illimité
        }

        $currentMarchands = \App\Models\Marchand::where('entreprise_id', $entrepriseId)
            ->count();

        return $currentMarchands < $maxMarchands;
    }

    /**
     * Vérifier si l'entreprise peut créer des utilisateurs
     */
    public function canCreateUser($entrepriseId)
    {
        if (!$this->hasAccess($entrepriseId, 'user_management')) {
            return false;
        }

        $maxUsers = $this->getModuleLimit($entrepriseId, 'user_management', 'max_users');

        if ($maxUsers === null) {
            return true; // Illimité
        }

        $currentUsers = \App\Models\User::where('entreprise_id', $entrepriseId)
            ->count();

        return $currentUsers < $maxUsers;
    }

    /**
     * Obtenir tous les modules accessibles pour une entreprise
     */
    public function getAccessibleModules($entrepriseId)
    {
        try {
            // Le dashboard est toujours accessible
            $modules = collect();
            $dashboardModule = Module::where('slug', 'dashboard')->first();
            if ($dashboardModule) {
                $modules->push($dashboardModule);
            }

            $subscription = SubscriptionPlan::getActiveSubscription($entrepriseId);

            if (!$subscription || !$subscription->pricing_plan_id) {
                return $modules;
            }

            $pricingPlan = PricingPlan::with('modules')->find($subscription->pricing_plan_id);

            if (!$pricingPlan) {
                return $modules;
            }

            $planModules = $pricingPlan->modules()
                ->wherePivot('is_enabled', true)
                ->get();

            return $modules->merge($planModules)->unique('id');
        } catch (\Exception $e) {
            Log::error('ModuleAccessService: Erreur lors de la récupération des modules', [
                'entreprise_id' => $entrepriseId,
                'error' => $e->getMessage()
            ]);
            // Retourner au moins le dashboard
            $dashboardModule = Module::where('slug', 'dashboard')->first();
            return $dashboardModule ? collect([$dashboardModule]) : collect();
        }
    }
}

