<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionHistory;
use App\Models\PricingPlan;
use App\Models\Entreprise;

class SubscriptionHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les entreprises existantes
        $entreprises = Entreprise::all();
        $pricingPlans = PricingPlan::all();

        if ($entreprises->isEmpty() || $pricingPlans->isEmpty()) {
            $this->command->warn('Aucune entreprise ou plan de tarification trouvé. Veuillez d\'abord exécuter les seeders pour les entreprises et les plans de tarification.');
            return;
        }

        $subscriptions = [];

        foreach ($entreprises as $entreprise) {
            // Créer plusieurs abonnements pour chaque entreprise
            $subscriptions[] = [
                'entreprise_id' => $entreprise->id,
                'pricing_plan_id' => $pricingPlans->where('name', 'Plan Starter')->first()->id,
                'plan_name' => 'Plan Starter',
                'price' => 29.99,
                'currency' => 'EUR',
                'start_date' => now()->subMonths(3)->startOfMonth(),
                'end_date' => now()->subMonths(2)->endOfMonth(),
                'status' => 'expired',
                'features' => [
                    'Jusqu\'à 100 livraisons/mois',
                    'Support email',
                    'Dashboard basique',
                    'Rapports mensuels'
                ],
                'payment_method' => 'carte_bancaire',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'notes' => 'Premier abonnement - période d\'essai'
            ];

            $subscriptions[] = [
                'entreprise_id' => $entreprise->id,
                'pricing_plan_id' => $pricingPlans->where('name', 'Plan Premium')->first()->id,
                'plan_name' => 'Plan Premium',
                'price' => 99.99,
                'currency' => 'EUR',
                'start_date' => now()->subMonths(2)->startOfMonth(),
                'end_date' => now()->subMonth()->endOfMonth(),
                'status' => 'expired',
                'features' => [
                    'Livraisons illimitées',
                    'Support 24/7',
                    'Analytics avancées',
                    'Intégrations API',
                    'Rapports personnalisés'
                ],
                'payment_method' => 'carte_bancaire',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'notes' => 'Upgrade vers Premium'
            ];

            $subscriptions[] = [
                'entreprise_id' => $entreprise->id,
                'pricing_plan_id' => $pricingPlans->where('name', 'Plan Enterprise')->first()->id,
                'plan_name' => 'Plan Enterprise',
                'price' => 299.99,
                'currency' => 'EUR',
                'start_date' => now()->subMonth()->startOfMonth(),
                'end_date' => now()->addMonths(11)->endOfMonth(),
                'status' => 'active',
                'features' => [
                    'Livraisons illimitées',
                    'Support dédié',
                    'Analytics avancées',
                    'API complète',
                    'White-label',
                    'Formation personnalisée'
                ],
                'payment_method' => 'virement_bancaire',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'notes' => 'Abonnement Enterprise actuel'
            ];

            // Ajouter quelques abonnements annulés
            if (rand(0, 1)) {
                $subscriptions[] = [
                    'entreprise_id' => $entreprise->id,
                    'pricing_plan_id' => $pricingPlans->where('name', 'Plan Premium')->first()->id,
                    'plan_name' => 'Plan Premium',
                    'price' => 99.99,
                    'currency' => 'EUR',
                    'start_date' => now()->subMonths(6)->startOfMonth(),
                    'end_date' => now()->subMonths(5)->endOfMonth(),
                    'status' => 'cancelled',
                    'features' => [
                        'Livraisons illimitées',
                        'Support 24/7',
                        'Analytics avancées',
                        'Intégrations API'
                    ],
                    'payment_method' => 'carte_bancaire',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'notes' => 'Abonnement annulé par le client'
                ];
            }
        }

        foreach ($subscriptions as $subscription) {
            SubscriptionHistory::create($subscription);
        }
    }
}
