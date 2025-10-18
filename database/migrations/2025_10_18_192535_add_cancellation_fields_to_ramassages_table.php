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
            $table->string('raison_annulation', 500)->nullable()->after('statut');
            $table->text('commentaire_annulation')->nullable()->after('raison_annulation');
            $table->timestamp('date_annulation')->nullable()->after('commentaire_annulation');
            $table->unsignedBigInteger('annule_par')->nullable()->after('date_annulation');

            // Index pour les recherches
            $table->index('date_annulation');
            $table->index('annule_par');

            // Clé étrangère pour annule_par (référence vers livreurs)
            $table->foreign('annule_par')->references('id')->on('livreurs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramassages', function (Blueprint $table) {
            $table->dropForeign(['annule_par']);
            $table->dropIndex(['date_annulation']);
            $table->dropIndex(['annule_par']);
            $table->dropColumn(['raison_annulation', 'commentaire_annulation', 'date_annulation', 'annule_par']);
        });
    }
};
