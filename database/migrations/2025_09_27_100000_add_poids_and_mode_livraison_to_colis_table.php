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
        // Cette migration peut être rejouée si un précédent `migrate` a échoué
        // (ex: colonne ajoutée mais FK non ajoutée). On évite donc les doublons.
        $hasPoidsId = Schema::hasColumn('colis', 'poids_id');
        $hasModeLivraisonId = Schema::hasColumn('colis', 'mode_livraison_id');

        if (!$hasPoidsId || !$hasModeLivraisonId) {
            Schema::table('colis', function (Blueprint $table) use ($hasPoidsId, $hasModeLivraisonId) {
                // `poids.id` et `mode_livraisons.id` sont créés avec `$table->id()` => unsignedBigInteger
                if (!$hasPoidsId) {
                    $table->unsignedBigInteger('poids_id')->nullable()->after('engin_id');
                }
                if (!$hasModeLivraisonId) {
                    $table->unsignedBigInteger('mode_livraison_id')->nullable()->after('poids_id');
                }
            });
        }

        // Aligner le type des colonnes avec les PK référencées (unsignedBigInteger)
        // même si Doctrine DBAL n'est pas installé (sinon l'ajout de FK peut échouer avec 1215).
        try {
            DB::statement('ALTER TABLE `colis` MODIFY `poids_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('ALTER TABLE `colis` MODIFY `mode_livraison_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
        }

        // Nettoyer les valeurs orphelines avant d'ajouter les FKs.
        try {
            DB::statement('
                UPDATE `colis` c
                LEFT JOIN `poids` p ON c.`poids_id` = p.`id`
                SET c.`poids_id` = NULL
                WHERE c.`poids_id` IS NOT NULL AND p.`id` IS NULL
            ');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('
                UPDATE `colis` c
                LEFT JOIN `mode_livraisons` m ON c.`mode_livraison_id` = m.`id`
                SET c.`mode_livraison_id` = NULL
                WHERE c.`mode_livraison_id` IS NOT NULL AND m.`id` IS NULL
            ');
        } catch (\Throwable $e) {
        }

        // Ajouter les clés étrangères dans une transaction séparée
        Schema::table('colis', function (Blueprint $table) {
            // On entoure de try/catch pour éviter d'échouer si la FK existe déjà
            // (ou si la colonne n'existe pas dans certains environnements).
            try {
                if (Schema::hasColumn('colis', 'poids_id')) {
                    $table->foreign('poids_id')->references('id')->on('poids')->onDelete('set null');
                }
            } catch (\Throwable $e) {
            }

            try {
                if (Schema::hasColumn('colis', 'mode_livraison_id')) {
                    $table->foreign('mode_livraison_id')->references('id')->on('mode_livraisons')->onDelete('set null');
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
            try { $table->dropForeign(['poids_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['mode_livraison_id']); } catch (\Throwable $e) {}
            $cols = [];
            if (Schema::hasColumn('colis', 'poids_id')) $cols[] = 'poids_id';
            if (Schema::hasColumn('colis', 'mode_livraison_id')) $cols[] = 'mode_livraison_id';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
