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
            $table->bigInteger('poids_id')->nullable()->after('engin_id');
            $table->bigInteger('mode_livraison_id')->nullable()->after('poids_id');
        });

        // Ajouter les clés étrangères dans une transaction séparée
        Schema::table('colis', function (Blueprint $table) {
            $table->foreign('poids_id')->references('id')->on('poids')->onDelete('set null');
            $table->foreign('mode_livraison_id')->references('id')->on('mode_livraisons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            $table->dropForeign(['poids_id']);
            $table->dropForeign(['mode_livraison_id']);
            $table->dropColumn(['poids_id', 'mode_livraison_id']);
        });
    }
};
