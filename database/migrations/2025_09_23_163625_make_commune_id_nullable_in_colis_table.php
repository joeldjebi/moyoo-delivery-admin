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
        Schema::table('colis', function (Blueprint $table) {
            // Rendre le champ commune_id nullable
            $table->unsignedBigInteger('commune_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            // Remettre le champ commune_id comme non-nullable
            $table->unsignedBigInteger('commune_id')->nullable(false)->change();
        });
    }
};
