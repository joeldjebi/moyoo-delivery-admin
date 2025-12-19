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
        // Migration rejouable : si la table existe déjà, on ne tente pas de la recréer.
        if (Schema::hasTable('categories')) {
            return;
        }

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED (sinon FK 1215)
            $table->unsignedBigInteger('entreprise_id');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // FKs/Index en mode safe (ordre de migration / contraintes déjà existantes)
            try {
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            } catch (\Throwable $e) {}
            $table->index('entreprise_id');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
