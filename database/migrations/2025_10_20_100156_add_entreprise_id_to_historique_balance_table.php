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
        Schema::table('historique_balance', function (Blueprint $table) {
            // La colonne entreprise_id existe déjà, on ajoute seulement la contrainte et l'index
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->index('entreprise_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historique_balance', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropIndex(['entreprise_id']);
            // On ne supprime pas la colonne car elle existait déjà
        });
    }
};
