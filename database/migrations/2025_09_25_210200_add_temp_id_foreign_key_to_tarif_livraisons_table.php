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
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->foreign('temp_id')->references('id')->on('temps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarif_livraisons', function (Blueprint $table) {
            $table->dropForeign(['temp_id']);
        });
    }
};
