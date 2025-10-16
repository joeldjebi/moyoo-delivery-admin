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
            $table->unsignedBigInteger('commune_depart_id')->nullable()->after('id');
        });

        // Mettre à jour les données existantes avec la première commune disponible
        $firstCommune = DB::table('communes')->first();
        if ($firstCommune) {
            DB::table('tarif_livraisons')->update(['commune_depart_id' => $firstCommune->id]);
        }

        // Maintenant ajouter la contrainte de clé étrangère
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->unsignedBigInteger('commune_depart_id')->nullable(false)->change();
            $table->foreign('commune_depart_id')->references('id')->on('communes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->dropForeign(['commune_depart_id']);
            $table->dropColumn('commune_depart_id');
        });
    }
};
