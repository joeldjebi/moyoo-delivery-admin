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
        Schema::create('balance_marchands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('marchand_id');
            $table->unsignedBigInteger('boutique_id');
            $table->decimal('montant_encaisse', 10, 2)->default(0.00);
            $table->decimal('montant_reverse', 10, 2)->default(0.00);
            $table->decimal('balance_actuelle', 10, 2)->default(0.00);
            $table->timestamp('derniere_mise_a_jour')->nullable();
            $table->timestamps();

            // Index et contraintes
            $table->unique(['marchand_id', 'boutique_id'], 'unique_marchand_boutique');
            $table->index('entreprise_id');
            $table->index('marchand_id');

            // Clés étrangères
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('cascade');
            $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_marchands');
    }
};
