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
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('marchand_id');
            $table->unsignedBigInteger('boutique_id');
            $table->decimal('montant_reverse', 10, 2);
            $table->enum('mode_reversement', ['especes', 'virement', 'mobile_money', 'cheque']);
            $table->string('reference_reversement')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'annule'])->default('en_attente');
            $table->timestamp('date_reversement')->nullable();
            $table->text('notes')->nullable();
            $table->string('justificatif_path')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamps();

            // Index
            $table->index('marchand_id');
            $table->index('statut');
            $table->index('date_reversement');
            $table->index('reference_reversement');

            // Clés étrangères
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('cascade');
            $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
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
