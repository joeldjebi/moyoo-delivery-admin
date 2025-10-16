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
        Schema::create('livreur_commune', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('livreur_id');
            $table->unsignedBigInteger('commune_id');
            $table->timestamps();

            // Clés étrangères
            $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade');
            $table->foreign('commune_id')->references('id')->on('communes')->onDelete('cascade');

            // Index unique pour éviter les doublons
            $table->unique(['livreur_id', 'commune_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreur_commune');
    }
};
