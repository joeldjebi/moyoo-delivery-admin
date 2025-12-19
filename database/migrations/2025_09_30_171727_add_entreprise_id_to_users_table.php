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
        // IMPORTANT: certains environnements ont une table `users` sans colonne `role`,
        // donc les `after('role')` peuvent faire échouer la migration.
        // On ajoute les colonnes de manière idempotente avec un `after(...)` seulement si la colonne cible existe.

        if (!Schema::hasColumn('users', 'entreprise_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('entreprise_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('entreprises')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('users', 'user_type')) {
            $after = Schema::hasColumn('users', 'role')
                ? 'role'
                : (Schema::hasColumn('users', 'status') ? 'status' : (Schema::hasColumn('users', 'email') ? 'email' : null));

            Schema::table('users', function (Blueprint $table) use ($after) {
                $col = $table->enum('user_type', ['super_admin', 'entreprise_admin', 'entreprise_user'])
                    ->default('entreprise_user');
                if ($after) {
                    $col->after($after);
                }
            });
        }

        if (!Schema::hasColumn('users', 'permissions')) {
            $after = Schema::hasColumn('users', 'user_type')
                ? 'user_type'
                : (Schema::hasColumn('users', 'role') ? 'role' : (Schema::hasColumn('users', 'email') ? 'email' : null));

            Schema::table('users', function (Blueprint $table) use ($after) {
                $col = $table->json('permissions')->nullable();
                if ($after) {
                    $col->after($after);
                }
            });
        }

        // Ajouter l'index si nécessaire
        Schema::table('users', function (Blueprint $table) {
            // Ajout d'index simple si la méthode hasIndex n'est pas disponible
            if (Schema::hasColumn('users', 'entreprise_id') && Schema::hasColumn('users', 'user_type')) {
                try {
                    $table->index(['entreprise_id', 'user_type']);
                } catch (\Throwable $e) {
                    // index déjà existant ou non supporté, ignorer
                }
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
