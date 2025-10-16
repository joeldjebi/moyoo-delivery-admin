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
        Schema::table('historique_livraisons', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');
            $table->unsignedBigInteger('package_colis_id')->nullable()->after('entreprise_id');

            // Rendre les colonnes nullable
            $table->unsignedBigInteger('livreur_id')->nullable()->change();
            $table->string('created_by')->nullable()->change();

            // Ajouter les contraintes de clés étrangères
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('package_colis_id')->references('id')->on('package_colis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historique_livraisons', function (Blueprint $table) {
            // Supprimer les contraintes de clés étrangères
            $table->dropForeign(['entreprise_id']);
            $table->dropForeign(['package_colis_id']);

            // Supprimer les colonnes ajoutées
            $table->dropColumn(['entreprise_id', 'package_colis_id']);

            // Remettre les colonnes en non-nullable (attention: peut échouer s'il y a des valeurs NULL)
            $table->unsignedBigInteger('livreur_id')->nullable(false)->change();
            $table->string('created_by')->nullable(false)->change();
        });
    }
};
