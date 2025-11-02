<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // livreur_locations -> livreur_id references users(id)
        if (Schema::hasTable('livreur_locations')) {
            Schema::table('livreur_locations', function (Blueprint $table) {
                try { $table->dropForeign(['livreur_id']); } catch (\Throwable $e) {}
            });
            Schema::table('livreur_locations', function (Blueprint $table) {
                try { $table->foreign('livreur_id')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }

        // livreur_location_status -> livreur_id references users(id)
        if (Schema::hasTable('livreur_location_status')) {
            Schema::table('livreur_location_status', function (Blueprint $table) {
                try { $table->dropForeign(['livreur_id']); } catch (\Throwable $e) {}
            });
            Schema::table('livreur_location_status', function (Blueprint $table) {
                try { $table->foreign('livreur_id')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('livreur_locations')) {
            Schema::table('livreur_locations', function (Blueprint $table) {
                try { $table->dropForeign(['livreur_id']); } catch (\Throwable $e) {}
            });
            Schema::table('livreur_locations', function (Blueprint $table) {
                try { $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('livreur_location_status')) {
            Schema::table('livreur_location_status', function (Blueprint $table) {
                try { $table->dropForeign(['livreur_id']); } catch (\Throwable $e) {}
            });
            Schema::table('livreur_location_status', function (Blueprint $table) {
                try { $table->foreign('livreur_id')->references('id')->on('livreurs')->onDelete('cascade'); } catch (\Throwable $e) {}
            });
        }
    }
};


