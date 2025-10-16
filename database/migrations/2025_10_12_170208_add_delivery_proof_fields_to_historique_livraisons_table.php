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
        Schema::table('historique_livraisons', function (Blueprint $table) {
            // Champs pour les preuves de livraison
            $table->string('code_validation_utilise')->nullable()->after('status')->comment('Code de validation utilisé lors de la livraison');
            $table->string('photo_proof_path')->nullable()->after('code_validation_utilise')->comment('Chemin vers la photo de preuve de livraison');
            $table->text('signature_data')->nullable()->after('photo_proof_path')->comment('Données de la signature (base64)');
            $table->text('note_livraison')->nullable()->after('signature_data')->comment('Note du livreur lors de la livraison');
            $table->string('motif_annulation')->nullable()->after('note_livraison')->comment('Motif d\'annulation si applicable');
            $table->timestamp('date_livraison_effective')->nullable()->after('motif_annulation')->comment('Date effective de la livraison');
            $table->decimal('latitude', 10, 8)->nullable()->after('date_livraison_effective')->comment('Latitude GPS de la livraison');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude')->comment('Longitude GPS de la livraison');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historique_livraisons', function (Blueprint $table) {
            $table->dropColumn([
                'code_validation_utilise',
                'photo_proof_path',
                'signature_data',
                'note_livraison',
                'motif_annulation',
                'date_livraison_effective',
                'latitude',
                'longitude'
            ]);
        });
    }
};
