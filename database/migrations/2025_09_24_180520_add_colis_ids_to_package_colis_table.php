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
            $table->text('colis_ids')->nullable()->after('communes_selected'); // JSON des IDs des colis créés
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_colis', function (Blueprint $table) {
            $table->dropColumn('colis_ids');
        });
    }
};
