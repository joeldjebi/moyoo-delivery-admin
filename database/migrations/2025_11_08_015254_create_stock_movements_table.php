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
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->bigInteger('entreprise_id');
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->enum('type', ['entree', 'sortie', 'ajustement', 'transfert']); // Type de mouvement
            $table->integer('quantity'); // Quantité (positive pour entrée, négative pour sortie)
            $table->decimal('unit_cost', 10, 2)->nullable(); // Coût unitaire au moment du mouvement
            $table->text('reason')->nullable(); // Raison du mouvement
            $table->string('reference')->nullable(); // Référence (bon de livraison, facture, etc.)
            $table->unsignedBigInteger('user_id'); // Utilisateur qui a effectué le mouvement
            $table->string('location')->nullable(); // Emplacement
            $table->integer('quantity_before')->default(0); // Quantité avant le mouvement
            $table->integer('quantity_after')->default(0); // Quantité après le mouvement
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('set null');
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('entreprise_id');
            $table->index('product_id');
            $table->index('type');
            $table->index('created_at');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
