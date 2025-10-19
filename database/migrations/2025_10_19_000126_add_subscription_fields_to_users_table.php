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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->onDelete('set null');
            $table->timestamp('subscription_started_at')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->enum('subscription_status', ['active', 'expired', 'cancelled', 'pending'])->default('active');
            $table->boolean('is_trial')->default(true); // Par défaut, l'utilisateur est en période d'essai
            $table->timestamp('trial_expires_at')->nullable(); // Fin de la période d'essai (1 mois)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn([
                'subscription_plan_id',
                'subscription_started_at',
                'subscription_expires_at',
                'subscription_status',
                'is_trial',
                'trial_expires_at'
            ]);
        });
    }
};
