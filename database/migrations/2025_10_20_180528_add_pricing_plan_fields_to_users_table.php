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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Ajouter le champ pour le plan de pricing actuel
                if (!Schema::hasColumn('users', 'current_pricing_plan_id')) {
                    $table->unsignedBigInteger('current_pricing_plan_id')->nullable()->after('subscription_plan_id');
                }
                // Ajouter une contrainte de clé étrangère
                try { $table->foreign('current_pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('set null'); } catch (\Throwable $e) {}
                // Ajouter un index
                try { $table->index('current_pricing_plan_id'); } catch (\Throwable $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                try { $table->dropForeign(['current_pricing_plan_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['current_pricing_plan_id']); } catch (\Throwable $e) {}
                if (Schema::hasColumn('users', 'current_pricing_plan_id')) {
                    $table->dropColumn('current_pricing_plan_id');
                }
            });
        }
    }
};
