<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pricing_plans')) {
            // La table n'existe pas, ne rien faire
            return;
        }

        $plans = [
            [
                'id' => 1,
                'name' => 'Free',
                'description' => 'Plan gratuit pour commencer',
                'price' => 0.00,
                'currency' => 'XOF',
                'period' => 'month',
                'features' => json_encode([
                    "Jusqu'à 20 colis par mois",
                    "Jusqu'à 2 livreurs",
                    "Jusqu'à 5 marchands",
                    "Support par email",
                    "Tableau de bord basique",
                    "Rapports mensuels",
                    "Suivi en temps réel"
                ]),
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => '2025-10-25 22:09:24',
                'updated_at' => '2025-10-25 22:09:24'
            ],
            [
                'id' => 2,
                'name' => 'Premium',
                'description' => 'Plan premium pour les entreprises en croissance',
                'price' => 25000.00,
                'currency' => 'XOF',
                'period' => 'month',
                'features' => json_encode([
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
                ]),
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => '2025-10-25 22:09:24',
                'updated_at' => '2025-10-25 22:09:24'
            ],
            [
                'id' => 3,
                'name' => 'Premium Annuel',
                'description' => 'Plan premium annuel avec remise',
                'price' => 250000.00,
                'currency' => 'XOF',
                'period' => 'year',
                'features' => json_encode([
                    "Colis illimités",
                    "Livreurs illimités",
                    "Marchands illimités",
                    "Support 24/7",
                    "Tableau de bord avancé",
                    "Rapports personnalisés",
                    "Suivi en temps réel",
                    "Notifications SMS",
                    "500 SMS WhatsApp",
                    "Accès API",
                    "Analyses avancées",
                    "Gestion multi-entrepôts",
                    "Formation en ligne",
                    "Moniteur Admin",
                    "Priorité nouvelles fonctionnalités",
                    "Facturation annuelle",
                    "Remise 16.7%"
                ]),
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => '2025-10-25 22:09:24',
                'updated_at' => '2025-10-25 22:09:24'
            ]
        ];

        foreach ($plans as $plan) {
            // Utiliser upsert pour éviter les doublons (compatible PostgreSQL et MySQL)
            DB::table('pricing_plans')->updateOrInsert(
                ['id' => $plan['id']],
                $plan
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les plans insérés
        DB::table('pricing_plans')->whereIn('id', [1, 2, 3])->delete();
    }
};
