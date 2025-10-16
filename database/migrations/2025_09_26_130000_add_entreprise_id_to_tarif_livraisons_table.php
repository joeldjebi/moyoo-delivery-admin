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
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->bigInteger('entreprise_id')->nullable()->after('commune_depart_id');
        });

        // Mettre à jour les données existantes avec l'ID de l'entreprise
        $entreprise = DB::table('entreprises')->first();
        if ($entreprise) {
            DB::table('tarif_livraisons')->update(['entreprise_id' => $entreprise->id]);
        }

        // Maintenant rendre la colonne non nullable et ajouter la clé étrangère
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->bigInteger('entreprise_id')->nullable(false)->change();
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropColumn('entreprise_id');
        });
    }
};
