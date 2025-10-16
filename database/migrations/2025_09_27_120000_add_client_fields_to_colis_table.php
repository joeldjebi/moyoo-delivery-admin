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
            $table->string('nom_client')->nullable()->after('status');
            $table->string('telephone_client')->nullable()->after('nom_client');
            $table->text('adresse_client')->nullable()->after('telephone_client');
            $table->decimal('montant_a_encaisse', 10, 2)->nullable()->after('adresse_client');
            $table->decimal('prix_de_vente', 10, 2)->nullable()->after('montant_a_encaisse');
            $table->string('numero_facture')->nullable()->after('prix_de_vente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            $table->dropColumn([
                'nom_client',
                'telephone_client',
                'adresse_client',
                'montant_a_encaisse',
                'prix_de_vente',
                'numero_facture'
            ]);
        });
    }
};
