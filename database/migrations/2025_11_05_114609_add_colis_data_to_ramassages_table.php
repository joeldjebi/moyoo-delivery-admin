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
        Schema::table('ramassages', function (Blueprint $table) {
            // Ajouter colis_data si elle n'existe pas déjà
            if (!Schema::hasColumn('ramassages', 'colis_data')) {
                $table->json('colis_data')->nullable()->after('notes')->comment('Données JSON des colis à ramasser');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramassages', function (Blueprint $table) {
            if (Schema::hasColumn('ramassages', 'colis_data')) {
                $table->dropColumn('colis_data');
            }
        });
    }
};
