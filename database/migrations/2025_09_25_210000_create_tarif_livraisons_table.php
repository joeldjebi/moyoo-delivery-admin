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
        Schema::create('tarif_livraisons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commune_id');
            $table->unsignedBigInteger('type_engin_id');
            $table->unsignedBigInteger('mode_livraison_id');
            $table->unsignedBigInteger('poids_id');
            $table->unsignedBigInteger('temp_id');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Clés étrangères
            $table->foreign('commune_id')->references('id')->on('communes')->onDelete('cascade');
            $table->foreign('type_engin_id')->references('id')->on('type_engins')->onDelete('cascade');
            $table->foreign('mode_livraison_id')->references('id')->on('mode_livraisons')->onDelete('cascade');
            $table->foreign('poids_id')->references('id')->on('poids')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_livraisons');
    }
};
