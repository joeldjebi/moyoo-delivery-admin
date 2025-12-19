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
        if (!Schema::hasTable('subscription_plans')) {
            return;
        }

        $driver = DB::getDriverName();
        $dbName = DB::getDatabaseName();
        $fkName = 'fk_subscription_plans_pricing_plan_id';
        $idxName = 'idx_subscription_plans_pricing_plan_id';

        // Compatible MySQL/PGSQL : pas de requêtes `pg_constraint` sous MySQL.
        // Migration rejouable : si la colonne existe déjà, on ne la recrée pas.
        if (!Schema::hasColumn('subscription_plans', 'pricing_plan_id')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedBigInteger('pricing_plan_id')->nullable()->after('entreprise_id');
            });
        }

        // Aligner le type côté MySQL même si DBAL n'est pas installé.
        try {
            DB::statement('ALTER TABLE `subscription_plans` MODIFY `pricing_plan_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }

        // Nettoyer les valeurs orphelines avant d'ajouter la FK
        try {
            DB::statement('
                UPDATE `subscription_plans` sp
                LEFT JOIN `pricing_plans` pp ON sp.`pricing_plan_id` = pp.`id`
                SET sp.`pricing_plan_id` = NULL
                WHERE sp.`pricing_plan_id` IS NOT NULL AND pp.`id` IS NULL
            ');
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('subscription_plans', 'pricing_plan_id') && Schema::hasTable('pricing_plans')) {
            // MySQL: éviter l'erreur 1022 (conflit nom index/constraint) en contrôlant l'existence
            // et en utilisant des noms explicites.
            $fkExists = false;
            $idxExists = false;

            if ($driver === 'mysql') {
                try {
                    $fkExists = (bool) DB::selectOne(
                        'SELECT 1 AS `x`
                         FROM information_schema.TABLE_CONSTRAINTS
                         WHERE CONSTRAINT_SCHEMA = ?
                           AND TABLE_NAME = ?
                           AND CONSTRAINT_NAME = ?
                           AND CONSTRAINT_TYPE = "FOREIGN KEY"
                         LIMIT 1',
                        [$dbName, 'subscription_plans', $fkName]
                    );
                } catch (\Throwable $e) {}

                try {
                    $idxExists = (bool) DB::selectOne(
                        'SELECT 1 AS `x`
                         FROM information_schema.STATISTICS
                         WHERE TABLE_SCHEMA = ?
                           AND TABLE_NAME = ?
                           AND INDEX_NAME = ?
                         LIMIT 1',
                        [$dbName, 'subscription_plans', $idxName]
                    );
                } catch (\Throwable $e) {}
            }

            // Assurer un index avec un nom stable (utile pour MySQL/InnoDB)
            if (!$idxExists) {
                Schema::table('subscription_plans', function (Blueprint $table) use ($idxName) {
                    try { $table->index('pricing_plan_id', $idxName); } catch (\Throwable $e) {}
                });
            }

            // Ajouter la FK seulement si elle n'existe pas déjà (et avec un nom stable)
            if (!$fkExists) {
                Schema::table('subscription_plans', function (Blueprint $table) use ($fkName) {
                    $table->foreign('pricing_plan_id', $fkName)
                        ->references('id')
                        ->on('pricing_plans')
                        ->onDelete('set null');
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
            try { $table->dropForeign(['pricing_plan_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('subscription_plans', 'pricing_plan_id')) {
                $table->dropColumn('pricing_plan_id');
            }
        });
    }
};
