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
        Schema::table('subscription_histories', function (Blueprint $table) {
            // Supprimer les anciennes colonnes
            $table->dropColumn([
                'entreprise_id',
                'plan_name',
                'price',
                'start_date',
                'end_date',
                'features',
                'notes'
            ]);

            // Ajouter les nouvelles colonnes
            $table->unsignedBigInteger('user_id')->after('id');
            $table->decimal('amount', 10, 2)->after('pricing_plan_id');
            $table->string('period')->default('month')->after('currency');
            $table->datetime('starts_at')->after('period');
            $table->datetime('expires_at')->after('starts_at');
            $table->boolean('is_trial')->default(false)->after('expires_at');
            $table->json('payment_data')->nullable()->after('transaction_id');

            // Ajouter les index et contraintes
            if (Schema::hasTable('users')) {
                try { $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            }
            $table->index(['user_id', 'status']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_histories', function (Blueprint $table) {
            // Supprimer les nouvelles colonnes
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['expires_at']);

            $table->dropColumn([
                'user_id',
                'amount',
                'period',
                'starts_at',
                'expires_at',
                'is_trial',
                'payment_data'
            ]);

            // Restaurer les anciennes colonnes
            $table->unsignedBigInteger('entreprise_id')->after('id');
            $table->string('plan_name')->after('pricing_plan_id');
            $table->decimal('price', 10, 2)->after('plan_name');
            $table->date('start_date')->after('price');
            $table->date('end_date')->after('start_date');
            $table->json('features')->nullable()->after('end_date');
            $table->text('notes')->nullable()->after('transaction_id');
        });
    }
};