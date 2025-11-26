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
        Schema::table('engins', function (Blueprint $table) {
            if (Schema::hasColumn('engins', 'matricule')) {
                $table->dropColumn('matricule');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('engins', function (Blueprint $table) {
            if (!Schema::hasColumn('engins', 'matricule')) {
                $table->string('matricule')->nullable()->after('libelle');
            }
        });
    }
};
