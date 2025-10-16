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
        Schema::table('delais', function (Blueprint $table) {
            // Modifier le type de la colonne pour correspondre à la table entreprises
            $table->bigInteger('entreprise_id')->unsigned(false)->change();
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delais', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->bigInteger('entreprise_id')->unsigned()->change();
        });
    }
};
