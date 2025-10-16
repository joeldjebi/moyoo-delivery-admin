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
            // S'assurer que les colonnes livreur_id et engin_id acceptent NULL
            $table->foreignId('livreur_id')->nullable()->change();
            $table->foreignId('engin_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            // Revenir à l'état précédent si nécessaire
            $table->foreignId('livreur_id')->nullable(false)->change();
            $table->foreignId('engin_id')->nullable(false)->change();
        });
    }
};
