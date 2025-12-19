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
        if (!Schema::hasTable('engins')) {
            return;
        }

        // Migration rejouable (si un migrate précédent a déjà ajouté la colonne mais a échoué sur la FK)
        if (!Schema::hasColumn('engins', 'entreprise_id')) {
            Schema::table('engins', function (Blueprint $table) {
                // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
                $table->unsignedBigInteger('entreprise_id')->nullable()->after('type_engin_id');
            });
        }

        // Aligner le type côté MySQL même si DBAL n'est pas installé.
        try { DB::statement('ALTER TABLE `engins` MODIFY `entreprise_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}

        // Nettoyer les valeurs orphelines avant d'ajouter la FK
        try {
            DB::statement('
                UPDATE `engins` en
                LEFT JOIN `entreprises` e ON en.`entreprise_id` = e.`id`
                SET en.`entreprise_id` = NULL
                WHERE en.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
            ');
        } catch (\Throwable $e) {}

        // Ajouter la FK + index en mode safe
        if (Schema::hasTable('entreprises') && Schema::hasColumn('engins', 'entreprise_id')) {
            Schema::table('engins', function (Blueprint $table) {
                try {
                    $table->foreign('entreprise_id')
                        ->references('id')
                        ->on('entreprises')
                        ->onDelete('cascade');
                } catch (\Throwable $e) {}
                try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engins', function (Blueprint $table) {
            // Supprimer la clé étrangère et l'index si ils existent
            if (Schema::hasColumn('engins', 'entreprise_id')) {
                try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['entreprise_id']); } catch (\Throwable $e) {}
                $table->dropColumn('entreprise_id');
            }
        });
    }
};
