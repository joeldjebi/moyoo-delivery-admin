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
            if (!Schema::hasColumn('users', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->after('id')->constrained('entreprises')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['super_admin', 'entreprise_admin', 'entreprise_user'])->default('entreprise_user')->after('role');
            }
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('user_type');
            }
        });

        // Ajouter l'index si nécessaire
        Schema::table('users', function (Blueprint $table) {
            // Ajout d'index simple si la méthode hasIndex n'est pas disponible
            if (Schema::hasColumn('users', 'entreprise_id') && Schema::hasColumn('users', 'user_type')) {
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
            if (Schema::hasColumn('users', 'entreprise_id')) {
                try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
            }
            try { $table->dropIndex(['entreprise_id', 'user_type']); } catch (\Throwable $e) {}
            $dropCols = [];
            foreach (['entreprise_id','user_type','permissions'] as $col) {
                if (Schema::hasColumn('users', $col)) { $dropCols[] = $col; }
            }
            if (!empty($dropCols)) { $table->dropColumn($dropCols); }
        });
    }
};
