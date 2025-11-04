<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entreprise;
use App\Models\SubscriptionPlan;
use App\Models\PricingPlan;
use Illuminate\Support\Facades\Log;

class FixMissingSubscriptionPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:fix-missing {--entreprise_id= : ID de l\'entreprise spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer les subscription_plans manquants pour les entreprises';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Recherche des entreprises sans subscription_plan...');

        // Récupérer le plan gratuit
        $pricingFree = PricingPlan::where('price', 0)->first();

        if (!$pricingFree) {
            $this->error('❌ Aucun plan gratuit trouvé dans pricing_plans. Exécutez d\'abord la migration pour seed les pricing_plans.');
            return 1;
        }

        // Récupérer les entreprises
        $entrepriseId = $this->option('entreprise_id');

        if ($entrepriseId) {
            $entreprises = Entreprise::where('id', $entrepriseId)->get();
        } else {
            $entreprises = Entreprise::all();
        }

        $created = 0;
        $updated = 0;
        $errors = 0;

        foreach ($entreprises as $entreprise) {
            try {
                // Vérifier si un subscription_plan existe déjà
                $existingPlan = SubscriptionPlan::where('entreprise_id', $entreprise->id)
                    ->where('slug', 'free')
                    ->first();

                $payload = [
                    'name' => $pricingFree->name ?? 'Free',
                    'slug' => 'free',
                    'entreprise_id' => $entreprise->id,
                    'description' => $pricingFree->description ?? 'Plan gratuit pour commencer',
                    'price' => $pricingFree->price ?? 0,
                    'currency' => $pricingFree->currency ?? 'XOF',
                    'duration_days' => 30,
                    'features' => $pricingFree->features ?? [
                        "Jusqu'à 20 colis par mois",
                        "Jusqu'à 2 livreurs",
                        "Jusqu'à 5 marchands",
                        "Support par email",
                        "Tableau de bord basique",
                        "Rapports mensuels",
                        "Suivi en temps réel"
                    ],
                    'max_colis_per_month' => 20,
                    'max_livreurs' => 2,
                    'max_marchands' => 5,
                    'whatsapp_notifications' => false,
                    'firebase_notifications' => false,
                    'api_access' => false,
                    'advanced_reports' => false,
                    'priority_support' => false,
                    'pricing_plan_id' => $pricingFree->id,
                    'is_active' => true,
                    'sort_order' => 1,
                    'started_at' => now(),
                    'expires_at' => now()->addYear(), // Plan gratuit = 1 an
                ];

                $subscriptionPlan = SubscriptionPlan::updateOrCreate(
                    ['entreprise_id' => $entreprise->id, 'slug' => 'free'],
                    $payload
                );

                if ($existingPlan) {
                    $updated++;
                    $this->info("✅ Subscription plan mis à jour pour l'entreprise #{$entreprise->id} ({$entreprise->name})");
                } else {
                    $created++;
                    $this->info("✅ Subscription plan créé pour l'entreprise #{$entreprise->id} ({$entreprise->name})");
                }

                Log::info('Subscription plan créé/mis à jour via commande', [
                    'entreprise_id' => $entreprise->id,
                    'subscription_plan_id' => $subscriptionPlan->id
                ]);

            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Erreur pour l'entreprise #{$entreprise->id}: {$e->getMessage()}");
                Log::error('Erreur lors de la création du subscription plan via commande', [
                    'entreprise_id' => $entreprise->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("📊 Résumé:");
        $this->info("   - Créés: {$created}");
        $this->info("   - Mis à jour: {$updated}");
        $this->info("   - Erreurs: {$errors}");

        return 0;
    }
}

