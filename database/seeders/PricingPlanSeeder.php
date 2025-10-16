<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingPlan;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Plan Starter',
                'description' => 'Parfait pour les petites entreprises qui commencent',
                'price' => 29.99,
                'currency' => 'EUR',
                'period' => 'month',
                'features' => [
                    'Jusqu\'à 100 livraisons/mois',
                    'Support email',
                    'Dashboard basique',
                    'Rapports mensuels',
                    'Suivi en temps réel'
                ],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Plan Premium',
                'description' => 'Idéal pour les entreprises en croissance',
                'price' => 99.99,
                'currency' => 'EUR',
                'period' => 'month',
                'features' => [
                    'Livraisons illimitées',
                    'Support 24/7',
                    'Analytics avancées',
                    'Intégrations API',
                    'Rapports personnalisés',
                    'Notifications SMS',
                    'Gestion multi-entrepôts'
                ],
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Plan Enterprise',
                'description' => 'Solution complète pour les grandes entreprises',
                'price' => 299.99,
                'currency' => 'EUR',
                'period' => 'month',
                'features' => [
                    'Livraisons illimitées',
                    'Support dédié',
                    'Analytics avancées',
                    'API complète',
                    'White-label',
                    'Formation personnalisée',
                    'Gestion multi-entrepôts',
                    'Intégrations personnalisées',
                    'SLA garanti'
                ],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Plan Annuel Starter',
                'description' => 'Plan Starter avec remise annuelle',
                'price' => 299.99,
                'currency' => 'EUR',
                'period' => 'year',
                'features' => [
                    'Jusqu\'à 100 livraisons/mois',
                    'Support email',
                    'Dashboard basique',
                    'Rapports mensuels',
                    'Suivi en temps réel',
                    'Économisez 2 mois'
                ],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Plan Annuel Premium',
                'description' => 'Plan Premium avec remise annuelle',
                'price' => 999.99,
                'currency' => 'EUR',
                'period' => 'year',
                'features' => [
                    'Livraisons illimitées',
                    'Support 24/7',
                    'Analytics avancées',
                    'Intégrations API',
                    'Rapports personnalisés',
                    'Notifications SMS',
                    'Gestion multi-entrepôts',
                    'Économisez 2 mois'
                ],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 5
            ]
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}
