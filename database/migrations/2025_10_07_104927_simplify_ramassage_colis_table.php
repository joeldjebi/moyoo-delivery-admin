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
        Schema::table('ramassage_colis', function (Blueprint $table) {
            // Supprimer les colonnes inutiles pour simplifier la table
            $table->dropColumn([
                'statut_colis',
                'notes_colis',
                'date_ramassage',
                'photo_ramassage'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramassage_colis', function (Blueprint $table) {
            // Restaurer les colonnes supprimées
            $table->string('statut_colis')->nullable()->after('colis_id');
            $table->text('notes_colis')->nullable()->after('statut_colis');
            $table->timestamp('date_ramassage')->nullable()->after('notes_colis');
            $table->string('photo_ramassage')->nullable()->after('date_ramassage');
        });
    }
};
