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
        Schema::create('frais_livraisons', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->decimal('montant', 10, 2);
            $table->string('type_frais'); // 'fixe', 'pourcentage', 'par_km', 'par_colis'
            $table->string('zone_applicable')->nullable(); // 'toutes', 'urbain', 'rural', 'specifique'
            $table->json('zones_specifiques')->nullable(); // Pour les zones spécifiques
            $table->boolean('actif')->default(true);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->bigInteger('entreprise_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['entreprise_id', 'actif']);
            $table->index(['type_frais', 'zone_applicable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_livraisons');
    }
};
