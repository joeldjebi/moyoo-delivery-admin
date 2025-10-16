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
        Schema::create('historique_balance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('balance_marchand_id');
            $table->enum('type_operation', ['encaissement', 'reversement', 'ajustement']);
            $table->decimal('montant', 10, 2);
            $table->decimal('balance_avant', 10, 2);
            $table->decimal('balance_apres', 10, 2);
            $table->text('description')->nullable();
            $table->string('reference')->nullable(); // ID du colis ou reversement
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Index
            $table->index('balance_marchand_id');
            $table->index('type_operation');
            $table->index('reference');

            // Clés étrangères
            $table->foreign('balance_marchand_id')->references('id')->on('balance_marchands')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
