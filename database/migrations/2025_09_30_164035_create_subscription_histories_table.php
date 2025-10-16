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
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('pricing_plan_id');
            $table->string('plan_name');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->json('features')->nullable(); // Array of features
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Clés étrangères - commentées temporairement pour éviter les erreurs
            // $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            // $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('cascade');
            $table->index(['entreprise_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
    }
};
