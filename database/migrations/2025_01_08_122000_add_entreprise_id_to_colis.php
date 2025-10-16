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
        Schema::table('colis', function (Blueprint $table) {
            // Ajouter la colonne entreprise_id
            $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');

            // Ajouter la contrainte de clé étrangère
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['entreprise_id']);

            // Supprimer la colonne
            $table->dropColumn('entreprise_id');
        });
    }
};
