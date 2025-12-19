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
        if (Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
            $table->unsignedBigInteger('entreprise_id');
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

            // FKs en mode safe (ordre de migration / contraintes déjà existantes)
            try { $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade'); } catch (\Throwable $e) {}
            try { $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade'); } catch (\Throwable $e) {}
            try { $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            $table->index('entreprise_id');
            $table->index('product_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
