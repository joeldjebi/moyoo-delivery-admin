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
            // Champs pour le suivi des différences entre estimé et réel
            $table->integer('difference_colis')->nullable()->after('nombre_colis_reel')->comment('Différence entre colis estimés et réels (positif = plus, négatif = moins)');
            $table->string('type_difference')->nullable()->after('difference_colis')->comment('Type de différence: plus, moins, ou null si pas de différence');
            $table->text('raison_difference')->nullable()->after('type_difference')->comment('Raison de la différence entre colis estimés et réels');

            // Champs pour le suivi du livreur
            $table->unsignedBigInteger('livreur_id')->nullable()->after('raison_difference')->comment('ID du livreur qui a effectué le ramassage');
            $table->timestamp('date_debut_ramassage')->nullable()->after('livreur_id')->comment('Date et heure de début du ramassage');
            $table->timestamp('date_fin_ramassage')->nullable()->after('date_debut_ramassage')->comment('Date et heure de fin du ramassage');

            // Champ pour la photo du ramassage
            $table->string('photo_ramassage')->nullable()->after('date_fin_ramassage')->comment('Chemin vers la photo du ramassage');

            // Champs pour les notes séparées
            $table->text('notes_livreur')->nullable()->after('photo_ramassage')->comment('Notes spécifiques du livreur');
            $table->text('notes_ramassage')->nullable()->after('notes_livreur')->comment('Notes sur le déroulement du ramassage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramassages', function (Blueprint $table) {
            $table->dropColumn([
                'difference_colis',
                'type_difference',
                'raison_difference',
                'livreur_id',
                'date_debut_ramassage',
                'date_fin_ramassage',
                'photo_ramassage',
                'notes_livreur',
                'notes_ramassage'
            ]);
        });
    }
};
