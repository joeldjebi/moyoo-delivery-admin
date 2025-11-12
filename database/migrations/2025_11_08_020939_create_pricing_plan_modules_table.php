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
        if (!Schema::hasTable('pricing_plan_modules')) {
            Schema::create('pricing_plan_modules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pricing_plan_id');
                $table->unsignedBigInteger('module_id');
                $table->boolean('is_enabled')->default(false);
                $table->json('limits')->nullable();
                $table->timestamps();

                $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->onDelete('cascade');
                $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
                $table->unique(['pricing_plan_id', 'module_id']);

                $table->index('pricing_plan_id');
                $table->index('module_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plan_modules');
    }
};
