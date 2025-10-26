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
                'name' => 'Free',
                'description' => 'Plan gratuit pour commencer',
                'price' => 0,
                'currency' => 'XOF',
                'period' => 'month',
                'features' => [
                    'Jusqu\'à 20 colis par mois',
                    'Jusqu\'à 2 livreurs',
                    'Jusqu\'à 5 marchands',
                    'Support par email',
                    'Tableau de bord basique',
                    'Rapports mensuels',
                    'Suivi en temps réel'
                ],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Premium',
                'description' => 'Plan premium pour les entreprises en croissance',
                'price' => 25000,
                'currency' => 'XOF',
                'period' => 'month',
                'features' => [
                    'Colis illimités',
                    'Livreurs illimités',
                    'Marchands illimités',
                    'Support 24/7',
                    'Tableau de bord avancé',
                    'Rapports personnalisés',
                    'Suivi en temps réel',
                    'Notifications SMS',
                    'Notifications WhatsApp',
                    'Accès API',
                    'Analyses avancées',
                    'Gestion multi-entrepôts',
                    'Formation en ligne',
                    'Moniteur Admin'
                ],
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Premium Annuel',
                'description' => 'Plan premium annuel avec remise',
                'price' => 250000,
                'currency' => 'XOF',
                'period' => 'year',
                'features' => [
                    'Colis illimités',
                    'Livreurs illimités',
                    'Marchands illimités',
                    'Support 24/7',
                    'Tableau de bord avancé',
                    'Rapports personnalisés',
                    'Suivi en temps réel',
                    'Notifications SMS',
                    '500 SMS WhatsApp',
                    'Accès API',
                    'Analyses avancées',
                    'Gestion multi-entrepôts',
                    'Formation en ligne',
                    'Moniteur Admin',
                    'Priorité nouvelles fonctionnalités',
                    'Facturation annuelle',
                    'Remise 16.7%'
                ],
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}
