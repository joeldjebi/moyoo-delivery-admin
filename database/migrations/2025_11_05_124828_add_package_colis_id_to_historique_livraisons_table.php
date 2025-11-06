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
        Schema::table('historique_livraisons', function (Blueprint $table) {
            // Ajouter package_colis_id si elle n'existe pas déjà
            if (!Schema::hasColumn('historique_livraisons', 'package_colis_id')) {
                $table->unsignedBigInteger('package_colis_id')->nullable()->after('livraison_id');

                // Ajouter la clé étrangère
                $table->foreign('package_colis_id')->references('id')->on('package_colis')->onDelete('cascade');

                // Ajouter l'index
                $table->index('package_colis_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historique_livraisons', function (Blueprint $table) {
            if (Schema::hasColumn('historique_livraisons', 'package_colis_id')) {
                // Supprimer la clé étrangère et l'index
                $table->dropForeign(['package_colis_id']);
                $table->dropIndex(['package_colis_id']);
                $table->dropColumn('package_colis_id');
            }
        });
    }
};
