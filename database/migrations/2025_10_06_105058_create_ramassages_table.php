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
        Schema::create('ramassages', function (Blueprint $table) {
            $table->id();
            $table->string('code_ramassage')->unique();
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('marchand_id');
            $table->unsignedBigInteger('boutique_id');
            $table->date('date_demande');
            $table->date('date_planifiee')->nullable();
            $table->date('date_effectuee')->nullable();
            $table->enum('statut', ['demande', 'planifie', 'en_cours', 'termine', 'annule'])->default('demande');
            $table->text('adresse_ramassage');
            $table->string('contact_ramassage');
            $table->string('telephone_contact');
            $table->integer('nombre_colis_estime')->default(0);
            $table->integer('nombre_colis_reel')->default(0);
            $table->text('notes')->nullable();
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->timestamps();

            // Contraintes de clés étrangères commentées temporairement
            // $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            // $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('cascade');
            // $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ramassages');
    }
};