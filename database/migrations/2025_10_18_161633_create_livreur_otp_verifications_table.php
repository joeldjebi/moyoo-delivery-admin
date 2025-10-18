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
        Schema::create('livreur_otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20); // Numéro de téléphone du livreur
            $table->string('otp_code', 4); // Code OTP de 4 chiffres
            $table->enum('type', ['password_reset', 'phone_verification'])->default('password_reset'); // Type de vérification
            $table->boolean('is_verified')->default(false); // Statut de vérification
            $table->timestamp('expires_at'); // Expiration du code
            $table->timestamp('verified_at')->nullable(); // Date de vérification
            $table->string('ip_address', 45)->nullable(); // Adresse IP
            $table->text('user_agent')->nullable(); // User agent
            $table->timestamps();

            // Index pour les recherches rapides
            $table->index(['mobile', 'otp_code']);
            $table->index(['mobile', 'is_verified']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreur_otp_verifications');
    }
};
