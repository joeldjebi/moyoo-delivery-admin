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
        if (!Schema::hasTable('entreprise_modules')) {
            Schema::create('entreprise_modules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('entreprise_id');
                $table->unsignedBigInteger('module_id');
                $table->decimal('price_paid', 10, 2)->default(0.00);
                $table->string('currency', 3)->default('XOF');
                $table->timestamp('purchased_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
                $table->unique(['entreprise_id', 'module_id']);

                $table->index('entreprise_id');
                $table->index('module_id');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprise_modules');
    }
};
