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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter seulement les colonnes qui n'existent pas
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['super_admin', 'entreprise_admin', 'entreprise_user'])->default('entreprise_user')->after('role');
            }
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('user_type');
            }
        });

        // Ajouter l'index si nécessaire
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', ['entreprise_id', 'user_type'])) {
                $table->index(['entreprise_id', 'user_type']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropIndex(['entreprise_id', 'user_type']);
            $table->dropColumn(['entreprise_id', 'user_type', 'permissions']);
        });
    }
};
