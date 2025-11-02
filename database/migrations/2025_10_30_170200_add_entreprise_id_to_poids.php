<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('poids')) return;
        Schema::table('poids', function (Blueprint $table) {
            if (!Schema::hasColumn('poids', 'entreprise_id')) {
                $table->bigInteger('entreprise_id')->after('created_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('poids')) return;
        Schema::table('poids', function (Blueprint $table) {
            if (Schema::hasColumn('poids', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
        });
    }
};


