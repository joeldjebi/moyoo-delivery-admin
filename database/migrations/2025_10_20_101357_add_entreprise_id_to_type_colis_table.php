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
        Schema::table('type_colis', function (Blueprint $table) {
            $table->bigInteger('entreprise_id')->nullable()->after('libelle');
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->index('entreprise_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_colis', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropIndex(['entreprise_id']);
            $table->dropColumn('entreprise_id');
        });
    }
};
