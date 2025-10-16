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
        Schema::table('planification_ramassage', function (Blueprint $table) {
            // Modifier le champ date_planifiee en datetime
            $table->datetime('date_planifiee')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planification_ramassage', function (Blueprint $table) {
            // Revenir au champ de type date
            $table->date('date_planifiee')->change();
        });
    }
};
