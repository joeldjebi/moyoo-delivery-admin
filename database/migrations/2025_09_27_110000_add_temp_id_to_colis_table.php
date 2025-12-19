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
        // Migration rejouable (si un migrate précédent a échoué à l'étape FK).
        if (!Schema::hasColumn('colis', 'temp_id')) {
            Schema::table('colis', function (Blueprint $table) {
                // `temps.id` est `$table->id()` => unsignedBigInteger
                $table->unsignedBigInteger('temp_id')->nullable()->after('mode_livraison_id');
            });
        }

        // Assurer l'alignement du type côté MySQL même si DBAL n'est pas installé
        // (sinon l'ajout de FK peut échouer avec 1215).
        try {
            DB::statement('ALTER TABLE `colis` MODIFY `temp_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // Ignore si driver non-MySQL ou si la colonne n'existe pas
        }

        // Nettoyer les valeurs orphelines avant d'ajouter la FK (sinon erreur 1215)
        try {
            DB::statement('
                UPDATE `colis` c
                LEFT JOIN `temps` t ON c.`temp_id` = t.`id`
                SET c.`temp_id` = NULL
                WHERE c.`temp_id` IS NOT NULL AND t.`id` IS NULL
            ');
        } catch (\Throwable $e) {
        }

        Schema::table('colis', function (Blueprint $table) {
            try {
                if (Schema::hasColumn('colis', 'temp_id')) {
                    $table->foreign('temp_id')->references('id')->on('temps')->onDelete('set null');
                }
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            try { $table->dropForeign(['temp_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('colis', 'temp_id')) {
                $table->dropColumn('temp_id');
            }
        });
    }
};
