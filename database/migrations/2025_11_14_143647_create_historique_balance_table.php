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
        if (Schema::hasTable('historique_balance')) {
            return;
        }

        Schema::create('historique_balance', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->unsignedBigInteger('balance_marchand_id');
            // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            // Informations de l'opération
            $table->string('type_operation', 50); // encaissement, reversement, ajustement
            $table->decimal('montant', 15, 2);
            $table->decimal('balance_avant', 15, 2);
            $table->decimal('balance_apres', 15, 2);
            $table->text('description')->nullable();
            $table->string('reference')->nullable(); // ID du colis ou reversement

            $table->timestamps();

            // Clés étrangères
            // FKs en mode safe (ordre de migration / contraintes déjà existantes)
            try {
                $table->foreign('balance_marchand_id')->references('id')->on('balance_marchands')->onDelete('cascade');
            } catch (\Throwable $e) {}
            if (Schema::hasTable('entreprises')) {
                try {
                    $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                } catch (\Throwable $e) {}
            }
            if (Schema::hasTable('users')) {
                try {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Throwable $e) {}
            }

            // Index pour les recherches
            $table->index('balance_marchand_id');
            $table->index('entreprise_id');
            $table->index('type_operation');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_balance');
    }
};
