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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_plan_id')->nullable()->after('entreprise_id');
            $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn('pricing_plan_id');
        });
    }
};
