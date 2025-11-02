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
        // Ajouter mobile à la table boutiques
        if (Schema::hasTable('boutiques')) {
            Schema::table('boutiques', function (Blueprint $table) {
                if (!Schema::hasColumn('boutiques', 'mobile')) {
                    $table->string('mobile', 20)->nullable()->after('libelle');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer mobile de la table boutiques
        if (Schema::hasTable('boutiques')) {
            Schema::table('boutiques', function (Blueprint $table) {
                if (Schema::hasColumn('boutiques', 'mobile')) {
                    $table->dropColumn('mobile');
                }
            });
        }
    }
};

