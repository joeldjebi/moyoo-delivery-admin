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
        if (!Schema::hasTable('livreur_locations')) {
            return;
        }

        if (!Schema::hasColumn('livreur_locations', 'entreprise_id')) {
            Schema::table('livreur_locations', function (Blueprint $table) {
                $table->unsignedBigInteger('entreprise_id')->nullable()->after('livreur_id');
            });

            // Mettre à jour les données existantes en récupérant l'entreprise_id depuis la table livreurs
            if (Schema::hasTable('livreurs') && Schema::hasColumn('livreurs', 'entreprise_id')) {
                DB::statement('
                    UPDATE livreur_locations 
                    SET entreprise_id = (
                        SELECT entreprise_id 
                        FROM livreurs 
                        WHERE livreurs.id = livreur_locations.livreur_id
                    )
                    WHERE entreprise_id IS NULL
                ');
            }

            // Ajouter la clé étrangère et l'index
            Schema::table('livreur_locations', function (Blueprint $table) {
                if (Schema::hasTable('entreprises')) {
                    $table->foreign('entreprise_id')
                        ->references('id')
                        ->on('entreprises')
                        ->onDelete('cascade');
                }
                $table->index('entreprise_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('livreur_locations')) {
            return;
        }

        if (Schema::hasColumn('livreur_locations', 'entreprise_id')) {
            Schema::table('livreur_locations', function (Blueprint $table) {
                // Supprimer la clé étrangère si elle existe
                $table->dropForeign(['entreprise_id']);
                $table->dropIndex(['entreprise_id']);
                $table->dropColumn('entreprise_id');
            });
        }
    }
};
