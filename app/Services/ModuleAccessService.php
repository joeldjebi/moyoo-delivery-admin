<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\PricingPlan;
use App\Models\Module;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            // Vérifier d'abord si le module existe et est actif
            $module = Module::where('slug', $moduleSlug)->first();

            // Si le module n'existe pas ou est désactivé par l'admin, refuser l'accès
            if (!$module || !$module->is_active) {
                Log::debug('ModuleAccessService: Module inexistant ou désactivé', [
                    'entreprise_id' => $entrepriseId,
                    'module_slug' => $moduleSlug,
                    'module_exists' => $module !== null,
                    'module_is_active' => $module ? $module->is_active : false,
                    'has_access' => false
                ]);
                return false;
            }

            // Si le module est non optionnel, il est accessible par défaut
            if (!$module->is_optional) {
                Log::debug('ModuleAccessService: Module non optionnel - Accès autorisé par défaut', [
                    'entreprise_id' => $entrepriseId,
                    'module_slug' => $moduleSlug,
                    'has_access' => true
                ]);
                return true;
            }

            // Pour les modules optionnels, vérifier s'ils ont été achetés directement
            if ($module->is_optional) {
                // Vérifier dans la table entreprise_modules
                $hasPurchasedModule = DB::table('entreprise_modules')
                    ->where('entreprise_id', $entrepriseId)
                    ->where('module_id', $module->id)
                    ->where('is_active', true)
                    ->exists();

                if ($hasPurchasedModule) {
                    Log::debug('ModuleAccessService: Module acheté directement', [
                        'entreprise_id' => $entrepriseId,
                        'module_slug' => $moduleSlug,
                        'has_access' => true
                    ]);
                    return true;
                }
            }

            // Sinon, vérifier via le pricing plan (ancien système)
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

            // Vérifier que le module est attaché au plan ET activé par l'admin (is_enabled = true)
            $hasModule = $pricingPlan->modules()
                ->where('slug', $moduleSlug)
                ->wherePivot('is_enabled', true)
                ->exists();

            Log::debug('ModuleAccessService: Vérification accès module', [
                'entreprise_id' => $entrepriseId,
                'module_slug' => $moduleSlug,
                'has_access' => $hasModule,
                'module_is_active' => $module->is_active,
                'pivot_is_enabled' => $hasModule
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

            // Récupérer les modules achetés directement (modules optionnels)
            // Vérifier si la table existe avant de l'utiliser
            try {
                if (DB::getSchemaBuilder()->hasTable('entreprise_modules')) {
                    $purchasedModules = DB::table('entreprise_modules')
                        ->where('entreprise_id', $entrepriseId)
                        ->where('is_active', true)
                        ->pluck('module_id');

                    if ($purchasedModules->isNotEmpty()) {
                        $purchasedModulesList = Module::whereIn('id', $purchasedModules)->get();
                        $modules = $modules->merge($purchasedModulesList);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('ModuleAccessService: Table entreprise_modules n\'existe pas encore', [
                    'entreprise_id' => $entrepriseId,
                    'error' => $e->getMessage()
                ]);
            }

            // Récupérer les modules via le pricing plan (ancien système)
            $subscription = SubscriptionPlan::getActiveSubscription($entrepriseId);
            $hasActiveSubscription = $subscription !== null;

            if ($subscription && $subscription->pricing_plan_id) {
                $pricingPlan = PricingPlan::with('modules')->find($subscription->pricing_plan_id);

                if ($pricingPlan) {
                    // Vérifier si le plan a des modules attachés explicitement
                    $planModules = $pricingPlan->modules()
                        ->wherePivot('is_enabled', true)
                        ->get();

                    // Si le plan a un prix > 0 (plan payant) et n'a pas de modules attachés,
                    // retourner tous les modules non optionnels par défaut
                    if ($planModules->isEmpty() && $pricingPlan->price > 0) {
                        // Récupérer tous les modules actifs non optionnels
                        $allModules = Module::where('is_active', true)
                            ->where(function($query) {
                                $query->where('is_optional', false)
                                      ->orWhereNull('is_optional');
                            })
                            ->get();

                        Log::info('ModuleAccessService: Plan Premium sans modules attachés - Retour de tous les modules non optionnels', [
                            'entreprise_id' => $entrepriseId,
                            'pricing_plan_id' => $pricingPlan->id,
                            'pricing_plan_name' => $pricingPlan->name,
                            'pricing_plan_price' => $pricingPlan->price,
                            'modules_count' => $allModules->count()
                        ]);

                        $modules = $modules->merge($allModules);
                    } else {
                        // Utiliser les modules attachés au plan
                        if ($planModules->isNotEmpty()) {
                            Log::info('ModuleAccessService: Modules attachés au plan trouvés', [
                                'entreprise_id' => $entrepriseId,
                                'pricing_plan_id' => $pricingPlan->id,
                                'pricing_plan_name' => $pricingPlan->name,
                                'modules_count' => $planModules->count(),
                                'module_slugs' => $planModules->pluck('slug')->toArray()
                            ]);
                            $modules = $modules->merge($planModules);
                            Log::info('ModuleAccessService: Modules fusionnés', [
                                'entreprise_id' => $entrepriseId,
                                'modules_count_after_merge' => $modules->count(),
                                'module_slugs_after_merge' => $modules->pluck('slug')->toArray()
                            ]);
                        } else {
                            Log::warning('ModuleAccessService: Aucun module attaché au plan', [
                                'entreprise_id' => $entrepriseId,
                                'pricing_plan_id' => $pricingPlan->id,
                                'pricing_plan_name' => $pricingPlan->name
                            ]);
                        }
                    }
                } else {
                    Log::warning('ModuleAccessService: Pricing plan non trouvé - Fallback: retour de tous les modules non optionnels', [
                        'entreprise_id' => $entrepriseId,
                        'pricing_plan_id' => $subscription->pricing_plan_id,
                        'subscription_name' => $subscription->name,
                        'subscription_price' => $subscription->price
                    ]);

                    // Fallback: si l'entreprise a un abonnement actif mais le pricing plan n'est pas trouvé,
                    // retourner tous les modules non optionnels si le prix de l'abonnement > 0
                    if ($subscription->price > 0) {
                        $allModules = Module::where('is_active', true)
                            ->where(function($query) {
                                $query->where('is_optional', false)
                                      ->orWhereNull('is_optional');
                            })
                            ->get();
                        $modules = $modules->merge($allModules);
                    }
                }
            } else {
                // Vérifier s'il y a un abonnement actif même sans pricing_plan_id
                if ($hasActiveSubscription && $subscription->price > 0) {
                    Log::info('ModuleAccessService: Abonnement actif sans pricing_plan_id - Retour de tous les modules non optionnels', [
                        'entreprise_id' => $entrepriseId,
                        'subscription_id' => $subscription->id,
                        'subscription_name' => $subscription->name,
                        'subscription_price' => $subscription->price
                    ]);

                    // Retourner tous les modules non optionnels
                    $allModules = Module::where('is_active', true)
                        ->where(function($query) {
                            $query->where('is_optional', false)
                                  ->orWhereNull('is_optional');
                        })
                        ->get();
                    $modules = $modules->merge($allModules);
                } else {
                    Log::warning('ModuleAccessService: Pas d\'abonnement actif', [
                        'entreprise_id' => $entrepriseId,
                        'has_subscription' => $hasActiveSubscription,
                        'has_pricing_plan_id' => $subscription && $subscription->pricing_plan_id ? true : false,
                        'subscription_price' => $subscription ? $subscription->price : null
                    ]);
                }
            }

            $uniqueModules = $modules->unique('id');

            Log::info('ModuleAccessService: Modules accessibles récupérés', [
                'entreprise_id' => $entrepriseId,
                'total_modules' => $uniqueModules->count(),
                'module_slugs' => $uniqueModules->pluck('slug')->toArray()
            ]);

            return $uniqueModules;
        } catch (\Exception $e) {
            Log::error('ModuleAccessService: Erreur lors de la récupération des modules', [
                'entreprise_id' => $entrepriseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Retourner au moins le dashboard
            $dashboardModule = Module::where('slug', 'dashboard')->first();
            return $dashboardModule ? collect([$dashboardModule]) : collect();
        }
    }
}

