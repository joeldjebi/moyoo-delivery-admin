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
        Schema::create('ramassage_colis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ramassage_id');
            $table->unsignedBigInteger('colis_id');
            $table->enum('statut_colis', ['attendu', 'ramasse', 'manquant', 'endommage', 'refuse'])->default('attendu');
            $table->text('notes_colis')->nullable();
            $table->timestamp('date_ramassage')->nullable();
            $table->timestamps();

            // Contraintes de clés étrangères commentées temporairement
            // $table->foreign('ramassage_id')->references('id')->on('ramassages')->onDelete('cascade');
            // $table->foreign('colis_id')->references('id')->on('colis')->onDelete('cascade');
            $table->unique(['ramassage_id', 'colis_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ramassage_colis');
    }
};
