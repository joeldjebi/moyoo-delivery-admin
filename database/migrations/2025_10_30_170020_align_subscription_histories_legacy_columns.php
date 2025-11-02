<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_histories')) {
            return;
        }
        Schema::table('subscription_histories', function (Blueprint $table) {
            // Ajouter les colonnes legacy si absentes (alignées sur le dump)
            if (!Schema::hasColumn('subscription_histories', 'entreprise_id')) {
                $table->unsignedBigInteger('entreprise_id')->after('id');
            }
            if (!Schema::hasColumn('subscription_histories', 'plan_name')) {
                $table->string('plan_name')->nullable()->after('pricing_plan_id');
            }
            if (!Schema::hasColumn('subscription_histories', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('plan_name');
            }
            if (!Schema::hasColumn('subscription_histories', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('price');
            }
            if (!Schema::hasColumn('subscription_histories', 'status')) {
                $table->string('status')->default('pending')->after('currency');
            }
            if (!Schema::hasColumn('subscription_histories', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }
            if (!Schema::hasColumn('subscription_histories', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('subscription_histories', 'is_extension')) {
                $table->boolean('is_extension')->default(false)->after('transaction_id');
            }
            if (!Schema::hasColumn('subscription_histories', 'extension_days')) {
                $table->integer('extension_days')->nullable()->after('is_extension');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscription_histories')) {
            return;
        }
        Schema::table('subscription_histories', function (Blueprint $table) {
            // Ne pas supprimer pour ne pas casser d'anciens rapports; pas de down strict
        });
    }
};