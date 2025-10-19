<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Free
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Plan gratuit avec fonctionnalités de base',
            'price' => 0.00,
            'currency' => 'XOF',
            'duration_days' => 30,
            'features' => [
                'Gestion de base des colis',
                'Jusqu\'à 50 colis par mois',
                'Jusqu\'à 2 livreurs',
                'Jusqu\'à 3 marchands',
                'Support par email'
            ],
            'max_colis_per_month' => 50,
            'max_livreurs' => 2,
            'max_marchands' => 3,
            'whatsapp_notifications' => false,
            'firebase_notifications' => true,
            'api_access' => false,
            'advanced_reports' => false,
            'priority_support' => false,
            'is_active' => true,
            'sort_order' => 1
        ]);

        // Plan Premium
        SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Plan premium avec toutes les fonctionnalités avancées',
            'price' => 12900.00,
            'currency' => 'XOF',
            'duration_days' => 30,
            'features' => [
                'Gestion complète des colis',
                'Colis illimités',
                'Livreurs illimités',
                'Marchands illimités',
                'Notifications WhatsApp',
                'Notifications Push',
                'Accès API complet',
                'Rapports avancés',
                'Support prioritaire'
            ],
            'max_colis_per_month' => null, // Illimité
            'max_livreurs' => null, // Illimité
            'max_marchands' => null, // Illimité
            'whatsapp_notifications' => true,
            'firebase_notifications' => true,
            'api_access' => true,
            'advanced_reports' => true,
            'priority_support' => true,
            'is_active' => true,
            'sort_order' => 2
        ]);
    }
}
