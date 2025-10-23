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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter le champ pour le plan de pricing actuel
            $table->unsignedBigInteger('current_pricing_plan_id')->nullable()->after('subscription_plan_id');

            // Ajouter une contrainte de clé étrangère
            $table->foreign('current_pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');

            // Ajouter un index
            $table->index('current_pricing_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_pricing_plan_id']);
            $table->dropIndex(['current_pricing_plan_id']);
            $table->dropColumn('current_pricing_plan_id');
        });
    }
};
