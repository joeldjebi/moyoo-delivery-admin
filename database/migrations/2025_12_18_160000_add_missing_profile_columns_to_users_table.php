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
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Colonnes nécessaires à l'inscription (utilisées par AuthController@verifyOTP)
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile')->nullable()->after('email');
            }

            // Colonnes fréquemment utilisées par l'app
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            foreach (['first_name', 'last_name', 'mobile', 'status', 'role'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $cols[] = $col;
                }
            }

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};


