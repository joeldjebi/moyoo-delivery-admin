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
        if (!Schema::hasTable('type_engins')) {
            return;
        }

        // Migration rejouable (si un migrate précédent a déjà ajouté la colonne mais a échoué sur la FK)
        if (!Schema::hasColumn('type_engins', 'entreprise_id')) {
            Schema::table('type_engins', function (Blueprint $table) {
                // `entreprises.id` est `$table->id()` => unsignedBigInteger (sinon FK 1215)
                $table->unsignedBigInteger('entreprise_id')->nullable()->after('libelle');
            });
        }

        // Aligner le type côté MySQL même si DBAL n'est pas installé.
        try {
            DB::statement('ALTER TABLE `type_engins` MODIFY `entreprise_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }

        // Nettoyer les valeurs orphelines avant d'ajouter la FK
        try {
            DB::statement('
                UPDATE `type_engins` te
                LEFT JOIN `entreprises` e ON te.`entreprise_id` = e.`id`
                SET te.`entreprise_id` = NULL
                WHERE te.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
            ');
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('type_engins', 'entreprise_id') && Schema::hasTable('entreprises')) {
            Schema::table('type_engins', function (Blueprint $table) {
                try {
                    $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                } catch (\Throwable $e) {}
                try {
                    $table->index('entreprise_id');
                } catch (\Throwable $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_engins', function (Blueprint $table) {
            try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['entreprise_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('type_engins', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
        });
    }
};
