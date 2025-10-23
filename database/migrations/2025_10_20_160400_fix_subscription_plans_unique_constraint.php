<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte d'unicité sur 'slug' seulement
            $table->dropUnique('subscription_plans_slug_unique');

            // Ajouter une nouvelle contrainte d'unicité composite sur 'slug' et 'entreprise_id'
            $table->unique(['slug', 'entreprise_id'], 'subscription_plans_slug_entreprise_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Supprimer la contrainte composite
            $table->dropUnique('subscription_plans_slug_entreprise_unique');

            // Restaurer l'ancienne contrainte d'unicité sur 'slug' seulement
            $table->unique('slug', 'subscription_plans_slug_unique');
        });
    }
};
