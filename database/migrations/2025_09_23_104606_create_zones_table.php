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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Libellés des communes séparés par des points-virgules (ex: "Cocody;Adjamé;Attécoubé;Yopougon")
            $table->text('description')->nullable();
            $table->integer('duree_estimee_minutes')->nullable(); // Durée estimée
            $table->decimal('distance_km', 8, 2)->nullable(); // Distance totale
            $table->boolean('actif')->default(true);
            $table->string('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
