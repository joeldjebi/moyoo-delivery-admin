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
        Schema::create('colis', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique(); // Code généré automatiquement : CLIS-XXXXXX-INITIALES_ZONE

            // Informations de base du colis
            $table->integer('status')->default(0)->comment('0: en attente, 1: en cours, 2: livré, 3: annulé par le client, 4: annulé par le livreur, 5: annulé par le marchand');
            $table->text('note_client')->nullable(); // Note spécifique pour ce colis
            $table->text('instructions_livraison')->nullable(); // Instructions spéciales pour ce colis

            // Relations avec zone et commune
            // La table des zones est 'zone_activites'
            $table->foreignId('zone_id')->constrained('zone_activites')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('commune_id')->constrained()->onUpdate('cascade')->onDelete('cascade');

            // Ordre et planification
            $table->integer('ordre_livraison')->nullable(); // Ordre dans la tournée
            $table->datetime('date_livraison_prevue')->nullable(); // Date prévue

            // Relations avec livreur et engin (assignés lors de l'optimisation)
            $table->foreignId('livreur_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('engin_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');

            // Métadonnées
            $table->string('created_by');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colis');
    }
};
