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
        Schema::create('commune_zone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->integer('ordre')->default(1); // Ordre des communes dans la zone

            // Informations client par défaut pour cette commune dans cette zone
            $table->string('nom_client')->nullable();
            $table->string('telephone_client')->nullable();
            $table->string('adresse_client')->nullable();

            // Informations marchand et boutique par défaut
            $table->foreignId('marchand_id')->nullable()->constrained('marchands')->nullOnDelete();
            $table->foreignId('boutique_id')->nullable()->constrained('boutiques')->nullOnDelete();

            // Informations produit par défaut
            $table->integer('montant_a_encaisse')->nullable();
            $table->integer('prix_de_vente')->nullable();
            $table->string('numero_facture')->nullable();

            // Informations technique par défaut
            $table->foreignId('type_colis_id')->nullable()->constrained('type_colis')->nullOnDelete();
            $table->foreignId('conditionnement_colis_id')->nullable()->constrained('conditionnement_colis')->nullOnDelete();
            $table->foreignId('poids_id')->nullable()->constrained('poids')->nullOnDelete();
            $table->foreignId('mode_livraison_id')->nullable()->constrained('mode_livraisons')->nullOnDelete();
            $table->foreignId('delai_id')->nullable()->constrained('delais')->nullOnDelete();

            // Informations ramassage par défaut
            $table->string('numero_de_ramassage')->nullable();
            $table->string('adresse_de_ramassage')->nullable();

            $table->timestamps();

            // Index unique pour éviter les doublons
            $table->unique(['zone_id', 'commune_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commune_zone');
    }
};
