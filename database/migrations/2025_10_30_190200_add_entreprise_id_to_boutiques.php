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
        // Ajouter entreprise_id à la table boutiques
        if (Schema::hasTable('boutiques')) {
            // Migration rejouable (si un migrate précédent a déjà ajouté la colonne mais a échoué sur la FK)
            if (!Schema::hasColumn('boutiques', 'entreprise_id')) {
                Schema::table('boutiques', function (Blueprint $table) {
                    // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
                    $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');
                });
            }

            // Aligner le type côté MySQL même si DBAL n'est pas installé.
            try { DB::statement('ALTER TABLE `boutiques` MODIFY `entreprise_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}

            // Nettoyer les valeurs orphelines avant d'ajouter la FK
            try {
                DB::statement('
                    UPDATE `boutiques` b
                    LEFT JOIN `entreprises` e ON b.`entreprise_id` = e.`id`
                    SET b.`entreprise_id` = NULL
                    WHERE b.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
                ');
            } catch (\Throwable $e) {}

            if (Schema::hasTable('entreprises') && Schema::hasColumn('boutiques', 'entreprise_id')) {
                Schema::table('boutiques', function (Blueprint $table) {
                    try {
                        $table->foreign('entreprise_id')
                            ->references('id')
                            ->on('entreprises')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                    } catch (\Throwable $e) {}
                    try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer entreprise_id de la table boutiques
        if (Schema::hasTable('boutiques')) {
            Schema::table('boutiques', function (Blueprint $table) {
                if (Schema::hasColumn('boutiques', 'entreprise_id')) {
                    try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                    $table->dropColumn('entreprise_id');
                }
            });
        }
    }
};
