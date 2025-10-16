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
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('numero_de_livraison');
            $table->foreignId('colis_id')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');
            $table->string('adresse_de_livraison');
            $table->integer('status')->default(0)->comment('0: en attente, 1: en cours, 2: livré, 3: annulé par le client, 4: annulé par le livreur, 5: annulé par le marchand');
            $table->string('note_livraison')->nullable();
            $table->string('code_validation')->nullable();
            $table->string('created_by');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};