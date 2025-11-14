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
        Schema::create('reversements', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('marchand_id');
            $table->unsignedBigInteger('boutique_id')->nullable();
            
            // Informations du reversement
            $table->decimal('montant_reverse', 15, 2);
            $table->string('mode_reversement', 50); // especes, virement, mobile_money, cheque
            $table->string('reference_reversement', 100)->unique();
            $table->string('statut', 20)->default('en_attente'); // en_attente, valide, annule
            $table->timestamp('date_reversement')->nullable();
            
            // Informations complémentaires
            $table->text('notes')->nullable();
            $table->string('justificatif_path')->nullable();
            
            // Traçabilité
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('validated_by')->nullable();
            
            $table->timestamps();
            
            // Index et clés étrangères
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('cascade');
            $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
            
            // Index pour les recherches
            $table->index('entreprise_id');
            $table->index('marchand_id');
            $table->index('boutique_id');
            $table->index('statut');
            $table->index('date_reversement');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reversements');
    }
};
