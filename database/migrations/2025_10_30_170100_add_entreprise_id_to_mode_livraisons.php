<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mode_livraisons')) return;

        Schema::table('mode_livraisons', function (Blueprint $table) {
            if (!Schema::hasColumn('mode_livraisons', 'description')) {
                $table->string('description', 300)->nullable()->after('libelle');
            }
            if (!Schema::hasColumn('mode_livraisons', 'entreprise_id')) {
                $table->bigInteger('entreprise_id')->after('created_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('mode_livraisons')) return;
        Schema::table('mode_livraisons', function (Blueprint $table) {
            if (Schema::hasColumn('mode_livraisons', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
            if (Schema::hasColumn('mode_livraisons', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};


