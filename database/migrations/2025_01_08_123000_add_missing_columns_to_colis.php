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
            // Ajouter toutes les colonnes manquantes
            $table->string('nom_client')->nullable()->after('instructions_livraison');
            $table->string('telephone_client')->nullable()->after('nom_client');
            $table->text('adresse_client')->nullable()->after('telephone_client');
            $table->decimal('montant_a_encaisse', 10, 2)->default(0)->after('adresse_client');
            $table->decimal('prix_de_vente', 10, 2)->default(0)->after('montant_a_encaisse');
            $table->string('numero_facture')->nullable()->after('prix_de_vente');
            $table->unsignedBigInteger('marchand_id')->nullable()->after('numero_facture');
            $table->unsignedBigInteger('boutique_id')->nullable()->after('marchand_id');
            $table->unsignedBigInteger('package_colis_id')->nullable()->after('boutique_id');
            $table->unsignedBigInteger('poids_id')->nullable()->after('package_colis_id');
            $table->unsignedBigInteger('mode_livraison_id')->nullable()->after('poids_id');
            $table->unsignedBigInteger('temp_id')->nullable()->after('mode_livraison_id');
            $table->string('numero_de_ramassage')->nullable()->after('temp_id');
            $table->text('adresse_de_ramassage')->nullable()->after('numero_de_ramassage');

            // Ajouter les contraintes de clés étrangères
            $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('set null');
            $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('set null');
            $table->foreign('package_colis_id')->references('id')->on('package_colis')->onDelete('set null');
            $table->foreign('poids_id')->references('id')->on('poids')->onDelete('set null');
            $table->foreign('mode_livraison_id')->references('id')->on('mode_livraisons')->onDelete('set null');
            $table->foreign('temp_id')->references('id')->on('temps')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            // Supprimer les contraintes de clés étrangères
            $table->dropForeign(['marchand_id']);
            $table->dropForeign(['boutique_id']);
            $table->dropForeign(['package_colis_id']);
            $table->dropForeign(['poids_id']);
            $table->dropForeign(['mode_livraison_id']);
            $table->dropForeign(['temp_id']);

            // Supprimer les colonnes
            $table->dropColumn([
                'nom_client',
                'telephone_client',
                'adresse_client',
                'montant_a_encaisse',
                'prix_de_vente',
                'numero_facture',
                'marchand_id',
                'boutique_id',
                'package_colis_id',
                'poids_id',
                'mode_livraison_id',
                'temp_id',
                'numero_de_ramassage',
                'adresse_de_ramassage'
            ]);
        });
    }
};
