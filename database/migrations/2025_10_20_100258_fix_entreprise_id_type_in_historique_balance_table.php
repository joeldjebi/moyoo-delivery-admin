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
        if (Schema::hasTable('historique_balance') && Schema::hasColumn('historique_balance', 'entreprise_id')) {
            Schema::table('historique_balance', function (Blueprint $table) {
                // Modifier le type de entreprise_id pour correspondre à la table entreprises
                $table->bigInteger('entreprise_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('historique_balance') && Schema::hasColumn('historique_balance', 'entreprise_id')) {
            Schema::table('historique_balance', function (Blueprint $table) {
                // Revenir au type unsigned (si pertinent en MySQL)
                try { $table->unsignedBigInteger('entreprise_id')->nullable()->change(); } catch (\Throwable $e) {}
            });
        }
    }
};
