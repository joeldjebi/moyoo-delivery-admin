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
        // Migration rejouable : si la table existe déjà, on ne tente pas de la recréer.
        if (Schema::hasTable('stocks')) {
            return;
        }

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
            $table->unsignedBigInteger('entreprise_id');
            $table->integer('quantity')->default(0); // Quantité en stock
            $table->integer('min_quantity')->default(0); // Seuil minimum d'alerte
            $table->integer('max_quantity')->nullable(); // Seuil maximum
            $table->decimal('unit_cost', 10, 2)->default(0.00); // Coût unitaire moyen
            $table->string('location')->nullable(); // Emplacement du stock (entrepôt, magasin, etc.)
            $table->timestamps();
            $table->softDeletes();

            // FKs en mode safe (ordre de migration / contraintes déjà existantes)
            try {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            } catch (\Throwable $e) {}
            $table->unique(['product_id', 'entreprise_id', 'location'], 'stock_unique');
            $table->index('entreprise_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
