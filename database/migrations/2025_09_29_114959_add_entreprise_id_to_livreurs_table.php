<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            // Ajouter le champ entreprise_id
            $table->bigInteger('entreprise_id')->nullable()->after('id');
        });

        // Mettre à jour les enregistrements existants avec entreprise_id = 1
        DB::table('livreurs')->update(['entreprise_id' => 1]);

        Schema::table('livreurs', function (Blueprint $table) {
            // Rendre le champ non-nullable
            $table->bigInteger('entreprise_id')->nullable(false)->change();

            // Ajouter la contrainte de clé étrangère
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropColumn('entreprise_id');
        });
    }
};
