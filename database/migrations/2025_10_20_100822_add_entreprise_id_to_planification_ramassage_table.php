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
        // Migration rejouable (si un migrate précédent a échoué au moment d'ajouter la FK)
        if (!Schema::hasColumn('planification_ramassage', 'entreprise_id')) {
            Schema::table('planification_ramassage', function (Blueprint $table) {
                // `entreprises.id` est `$table->id()` => unsignedBigInteger (sinon FK 1215)
                $table->unsignedBigInteger('entreprise_id')->nullable()->after('livreur_id');
            });
        }

        // Aligner le type côté MySQL même si DBAL n'est pas installé
        try {
            DB::statement('ALTER TABLE `planification_ramassage` MODIFY `entreprise_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }

        // Nettoyer les valeurs orphelines avant d'ajouter la FK
        try {
            DB::statement('
                UPDATE `planification_ramassage` pr
                LEFT JOIN `entreprises` e ON pr.`entreprise_id` = e.`id`
                SET pr.`entreprise_id` = NULL
                WHERE pr.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
            ');
        } catch (\Throwable $e) {
        }

        Schema::table('planification_ramassage', function (Blueprint $table) {
            try {
                $table->foreign('entreprise_id')
                    ->references('id')
                    ->on('entreprises')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {
            }

            try {
                $table->index('entreprise_id');
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planification_ramassage', function (Blueprint $table) {
            try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['entreprise_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('planification_ramassage', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
        });
    }
};
