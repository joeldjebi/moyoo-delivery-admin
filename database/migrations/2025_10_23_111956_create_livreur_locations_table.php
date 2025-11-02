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
        Schema::create('livreur_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('livreur_id');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('altitude', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 8, 2)->nullable();
            $table->timestamp('timestamp');
            $table->enum('status', ['en_cours', 'en_pause', 'termine'])->default('en_cours');
            $table->unsignedBigInteger('ramassage_id')->nullable();
            $table->unsignedBigInteger('historique_livraison_id')->nullable();
            $table->timestamps();

            $table->index('livreur_id');
            $table->index('timestamp');
            $table->index('status');
            $table->index(['livreur_id', 'timestamp']);
            // Référence vers la table des livreurs
            $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade');
            $table->foreign('ramassage_id')->references('id')->on('ramassages')->onDelete('set null');
            $table->foreign('historique_livraison_id')->references('id')->on('historique_livraisons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreur_locations');
    }
};
