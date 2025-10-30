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
            'description' => 'Plan gratuit',
            'price' => 0.00,
            'currency' => 'XOF',
            'duration_days' => 30,
            'features' => [
                'Accès de base à la plateforme'
            ],
            'max_colis_per_month' => 5,
            'max_livreurs' => 2,
            'max_marchands' => 2,
            'whatsapp_notifications' => false,
            'whatsapp_sms_limit' => null,
            'firebase_notifications' => true,
            'api_access' => false,
            'advanced_reports' => false,
            'priority_support' => false,
            'is_active' => true,
            'sort_order' => 1,
            'entreprise_id' => null,
            'pricing_plan_id' => null,
            'started_at' => null,
            'expires_at' => null
        ]);

        // Plan Premium
        SubscriptionPlan::create([
            'name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Plan premium',
            'price' => 12900.00,
            'currency' => 'XOF',
            'duration_days' => 30,
            'features' => [
                'Accès complet à toutes les fonctionnalités'
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
            'pricing_plan_id' => null,
            'started_at' => null,
            'expires_at' => null
        ]);
    }
}