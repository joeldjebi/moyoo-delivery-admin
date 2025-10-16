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
            $table->unsignedBigInteger('package_colis_id')->nullable()->after('id');
            $table->foreign('package_colis_id')->references('id')->on('package_colis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colis', function (Blueprint $table) {
            $table->dropForeign(['package_colis_id']);
            $table->dropColumn('package_colis_id');
        });
    }
};
