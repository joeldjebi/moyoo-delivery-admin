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
        Schema::create('package_colis', function (Blueprint $table) {
            $table->id();
            $table->string('numero_package')->unique(); // Code unique du package
            $table->unsignedBigInteger('marchand_id');
            $table->unsignedBigInteger('boutique_id');
            $table->integer('nombre_colis'); // Nombre de colis dans ce package
            $table->text('communes_selected'); // JSON des communes sélectionnées
            $table->unsignedBigInteger('livreur_id')->nullable(); // Livreur assigné (optionnel)
            $table->unsignedBigInteger('engin_id')->nullable(); // Engin assigné (optionnel)
            $table->enum('statut', ['en_attente', 'en_cours', 'livre', 'annule'])->default('en_attente');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Clés étrangères
            $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('cascade');
            $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('cascade');
            $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('set null');
            $table->foreign('engin_id')->references('id')->on('engins')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_colis');
    }
};
