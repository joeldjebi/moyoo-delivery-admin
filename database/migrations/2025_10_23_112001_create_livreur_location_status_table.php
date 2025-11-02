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
        Schema::create('livreur_location_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('livreur_id');
            $table->enum('status', ['active', 'inactive', 'paused'])->default('inactive');
            $table->string('socket_id')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();

            $table->unique('livreur_id');
            // Référence vers la table des livreurs
            $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreur_location_status');
    }
};
