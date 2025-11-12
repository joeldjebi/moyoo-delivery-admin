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
        if (!Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->bigInteger('entreprise_id');
                $table->integer('quantity')->default(0);
                $table->integer('min_quantity')->default(0);
                $table->integer('max_quantity')->nullable();
                $table->decimal('unit_cost', 10, 2)->default(0.00);
                $table->string('location')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                $table->unique(['product_id', 'entreprise_id', 'location'], 'stock_unique');
                $table->index('entreprise_id');
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->bigInteger('entreprise_id');
                $table->unsignedBigInteger('stock_id')->nullable();
                $table->enum('type', ['entree', 'sortie', 'ajustement', 'transfert']);
                $table->integer('quantity');
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->text('reason')->nullable();
                $table->string('reference')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->string('location')->nullable();
                $table->integer('quantity_before')->default(0);
                $table->integer('quantity_after')->default(0);
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('set null');
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('entreprise_id');
                $table->index('product_id');
                $table->index('type');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
    }
};
