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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Premium
            $table->string('slug')->unique(); // free, premium
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0); // Prix en FCFA
            $table->string('currency', 3)->default('XOF'); // FCFA
            $table->integer('duration_days')->default(30); // Durée en jours
            $table->json('features')->nullable(); // Fonctionnalités incluses
            $table->integer('max_colis_per_month')->nullable(); // Limite de colis par mois
            $table->integer('max_livreurs')->nullable(); // Limite de livreurs
            $table->integer('max_marchands')->nullable(); // Limite de marchands
            $table->boolean('whatsapp_notifications')->default(false);
            $table->boolean('firebase_notifications')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('advanced_reports')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
