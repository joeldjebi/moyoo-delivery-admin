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
        Schema::table('package_colis', function (Blueprint $table) {
            // Ajouter entreprise_id si elle n'existe pas déjà
            if (!Schema::hasColumn('package_colis', 'entreprise_id')) {
                $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');

                // Ajouter la clé étrangère
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');

                // Ajouter l'index
                $table->index('entreprise_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_colis', function (Blueprint $table) {
            if (Schema::hasColumn('package_colis', 'entreprise_id')) {
                // Supprimer la clé étrangère et l'index
                $table->dropForeign(['entreprise_id']);
                $table->dropIndex(['entreprise_id']);
                $table->dropColumn('entreprise_id');
            }
        });
    }
};
