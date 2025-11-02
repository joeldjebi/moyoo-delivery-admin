<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('temps')) return;
        Schema::table('temps', function (Blueprint $table) {
            if (!Schema::hasColumn('temps', 'entreprise_id')) {
                $table->bigInteger('entreprise_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('temps')) return;
        Schema::table('temps', function (Blueprint $table) {
            if (Schema::hasColumn('temps', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
        });
    }
};


