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
        Schema::table('livreurs', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte de clé étrangère
            $table->dropForeign(['zone_activite_id']);

            // Ajouter la nouvelle contrainte vers la table communes
            $table->foreign('zone_activite_id')->references('id')->on('communes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            // Supprimer la contrainte vers communes
            $table->dropForeign(['zone_activite_id']);

            // Remettre l'ancienne contrainte vers zone_activites
            $table->foreign('zone_activite_id')->references('id')->on('zone_activites')->onDelete('cascade');
        });
    }
};
