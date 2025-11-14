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
        Schema::table('engins', function (Blueprint $table) {
            // Vérifier si la colonne n'existe pas déjà
            if (!Schema::hasColumn('engins', 'entreprise_id')) {
                $table->bigInteger('entreprise_id')->nullable()->after('type_engin_id');
                
                // Ajouter la clé étrangère si la table entreprises existe
                if (Schema::hasTable('entreprises')) {
                    $table->foreign('entreprise_id')
                          ->references('id')
                          ->on('entreprises')
                          ->onDelete('cascade');
                    $table->index('entreprise_id');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engins', function (Blueprint $table) {
            // Supprimer la clé étrangère et l'index si ils existent
            if (Schema::hasColumn('engins', 'entreprise_id')) {
                $table->dropForeign(['entreprise_id']);
                $table->dropIndex(['entreprise_id']);
                $table->dropColumn('entreprise_id');
            }
        });
    }
};
