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
        Schema::table('ramassages', function (Blueprint $table) {
            // Modifier les champs de date en datetime
            $table->datetime('date_demande')->change();
            $table->datetime('date_planifiee')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramassages', function (Blueprint $table) {
            // Revenir aux champs de type date
            $table->date('date_demande')->change();
            $table->date('date_planifiee')->nullable()->change();
        });
    }
};
