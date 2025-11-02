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
        // Ajouter entreprise_id à la table boutiques
        if (Schema::hasTable('boutiques')) {
            Schema::table('boutiques', function (Blueprint $table) {
                if (!Schema::hasColumn('boutiques', 'entreprise_id')) {
                    $table->bigInteger('entreprise_id')->nullable()->after('id');
                    $table->foreign('entreprise_id')
                          ->references('id')
                          ->on('entreprises')
                          ->onDelete('cascade')
                          ->onUpdate('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer entreprise_id de la table boutiques
        if (Schema::hasTable('boutiques')) {
            Schema::table('boutiques', function (Blueprint $table) {
                if (Schema::hasColumn('boutiques', 'entreprise_id')) {
                    $table->dropForeign(['entreprise_id']);
                    $table->dropColumn('entreprise_id');
                }
            });
        }
    }
};
