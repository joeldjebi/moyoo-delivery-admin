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
        if (Schema::hasTable('products')) {
            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable(); // Stock Keeping Unit
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id');
            // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
            $table->unsignedBigInteger('entreprise_id');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('XOF');
            $table->string('unit')->default('unité'); // unité, kg, litre, etc.
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // FKs en mode safe (ordre de migration / contraintes déjà existantes)
            try {
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            } catch (\Throwable $e) {}
            $table->index('entreprise_id');
            $table->index('category_id');
            $table->index('sku');
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
