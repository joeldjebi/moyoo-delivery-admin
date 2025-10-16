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
        Schema::table('temps', function (Blueprint $table) {
            // Ajouter les champs manquants
            $table->time('heure_debut')->nullable()->after('description');
            $table->time('heure_fin')->nullable()->after('heure_debut');
            $table->boolean('is_weekend')->default(false)->after('heure_fin');
            $table->boolean('is_holiday')->default(false)->after('is_weekend');
            $table->boolean('is_active')->default(true)->after('is_holiday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temps', function (Blueprint $table) {
            $table->dropColumn(['heure_debut', 'heure_fin', 'is_weekend', 'is_holiday', 'is_active']);
        });
    }
};
