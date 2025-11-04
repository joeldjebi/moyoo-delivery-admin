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
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'pricing_plan_id')) {
                $table->unsignedBigInteger('pricing_plan_id')->nullable()->after('entreprise_id');
            }
        });

        // Vérifier et ajouter la foreign key si elle n'existe pas
        if (Schema::hasColumn('subscription_plans', 'pricing_plan_id')) {
            $constraintExists = DB::selectOne("
                SELECT 1
                FROM pg_constraint
                WHERE conrelid = 'subscription_plans'::regclass
                AND contype = 'f'
                AND conname = 'subscription_plans_pricing_plan_id_foreign'
            ");

            if (!$constraintExists) {
                Schema::table('subscription_plans', function (Blueprint $table) {
                    $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn('pricing_plan_id');
        });
    }
};
