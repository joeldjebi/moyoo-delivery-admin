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
        Schema::table('ramassage_colis', function (Blueprint $table) {
            $table->string('photo_ramassage')->nullable()->after('notes_colis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramassage_colis', function (Blueprint $table) {
            $table->dropColumn('photo_ramassage');
        });
    }
};
