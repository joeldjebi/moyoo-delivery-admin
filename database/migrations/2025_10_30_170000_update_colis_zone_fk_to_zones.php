<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('colis')) {
            return;
        }

        Schema::table('colis', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte si elle référence zone_activites
            try { $table->dropForeign(['zone_id']); } catch (\Throwable $e) {}
        });

        Schema::table('colis', function (Blueprint $table) {
            // Recréer la FK vers 'zones' selon le dump MySQL
            try { $table->foreign('zone_id')->references('id')->on('zones')->onUpdate('cascade')->onDelete('cascade'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('colis')) {
            return;
        }
        Schema::table('colis', function (Blueprint $table) {
            try { $table->dropForeign(['zone_id']); } catch (\Throwable $e) {}
        });
        Schema::table('colis', function (Blueprint $table) {
            try { $table->foreign('zone_id')->references('id')->on('zone_activites')->onUpdate('cascade')->onDelete('cascade'); } catch (\Throwable $e) {}
        });
    }
};


