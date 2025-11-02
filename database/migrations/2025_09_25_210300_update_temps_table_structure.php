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
        // Ajouter uniquement les colonnes manquantes (compatibilité multi-exécutions)
        if (!Schema::hasColumn('temps', 'heure_debut')) {
            Schema::table('temps', function (Blueprint $table) {
                $table->time('heure_debut')->nullable()->after('description');
            });
        }
        if (!Schema::hasColumn('temps', 'heure_fin')) {
            Schema::table('temps', function (Blueprint $table) {
                $table->time('heure_fin')->nullable()->after('heure_debut');
            });
        }
        if (!Schema::hasColumn('temps', 'is_weekend')) {
            Schema::table('temps', function (Blueprint $table) {
                $table->boolean('is_weekend')->default(false)->after('heure_fin');
            });
        }
        if (!Schema::hasColumn('temps', 'is_holiday')) {
            Schema::table('temps', function (Blueprint $table) {
                $table->boolean('is_holiday')->default(false)->after('is_weekend');
            });
        }
        if (!Schema::hasColumn('temps', 'is_active')) {
            Schema::table('temps', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('is_holiday');
            });
        }
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
