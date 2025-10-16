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
        Schema::create('planification_ramassage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ramassage_id');
            $table->unsignedBigInteger('livreur_id');
            $table->date('date_planifiee');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('zone_ramassage');
            $table->integer('ordre_visite')->default(1);
            $table->enum('statut_planification', ['planifie', 'en_cours', 'termine', 'annule'])->default('planifie');
            $table->text('notes_planification')->nullable();
            $table->timestamps();

            // Contraintes de clés étrangères commentées temporairement
            // $table->foreign('ramassage_id')->references('id')->on('ramassages')->onDelete('cascade');
            // $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planification_ramassage');
    }
};
