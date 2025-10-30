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
        // Plan Free (aligné sur pricing_plans id=1)
        SubscriptionPlan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Plan gratuit pour commencer',
            'price' => 0.00,
            'currency' => 'XOF',
            'duration_days' => 30,
            'features' => [
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
            'whatsapp_sms_limit' => null,
            'firebase_notifications' => true,
            'api_access' => false,
            'advanced_reports' => false,
            'priority_support' => false,
            'is_active' => true,
            'sort_order' => 1,
            'entreprise_id' => null,
            'pricing_plan_id' => 1,
            'started_at' => null,
            'expires_at' => null
        ]);

        // Plan Premium
        SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Plan premium pour les entreprises en croissance',
            'price' => 25000.00,
            'currency' => 'XOF',
            'duration_days' => 30,
            'features' => [
                "Colis illimités",
                "Livreurs illimités",
                "Marchands illimités",
                "Support 24/7",
                "Tableau de bord avancé",
                "Rapports personnalisés",
                "Suivi en temps réel",
                "Notifications SMS",
                "Notifications WhatsApp",
                "Accès API",
                "Analyses avancées",
                "Gestion multi-entrepôts",
                "Formation en ligne",
                "Moniteur Admin"
            ],
            'max_colis_per_month' => null,
            'max_livreurs' => null,
            'max_marchands' => null,
            'whatsapp_notifications' => true,
            'whatsapp_sms_limit' => null,
            'firebase_notifications' => true,
            'api_access' => true,
            'advanced_reports' => true,
            'priority_support' => true,
            'is_active' => true,
            'sort_order' => 2,
            'entreprise_id' => null,
            'pricing_plan_id' => 2,
            'started_at' => null,
            'expires_at' => null
        ]);
    }
}