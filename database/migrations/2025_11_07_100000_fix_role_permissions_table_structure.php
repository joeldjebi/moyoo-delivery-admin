<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la table existe et quelle est sa structure actuelle
        $tableExists = Schema::hasTable('role_permissions');
        $driver = DB::getDriverName();
        $dbName = DB::getDatabaseName();

        if ($tableExists) {
            // Vérifier si la table a la structure pivot (role_id, permission_id)
            $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
            $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');
            $hasRole = Schema::hasColumn('role_permissions', 'role');
            $hasPermissions = Schema::hasColumn('role_permissions', 'permissions');
            $hasEntrepriseId = Schema::hasColumn('role_permissions', 'entreprise_id');

            // Si la table a la structure pivot, la transformer
            if ($hasRoleId && $hasPermissionId && !$hasRole && !$hasPermissions) {
                // Supprimer les données existantes (structure pivot incompatible)
                DB::table('role_permissions')->truncate();

                // Supprimer les contraintes existantes
                try {
                    if ($driver === 'mysql') {
                        // MySQL: récupérer les noms réels des FKs sur role_id/permission_id puis les supprimer
                        $fks = DB::select(
                            'SELECT CONSTRAINT_NAME AS fk
                             FROM information_schema.KEY_COLUMN_USAGE
                             WHERE TABLE_SCHEMA = ?
                               AND TABLE_NAME = ?
                               AND COLUMN_NAME IN ("role_id", "permission_id")
                               AND REFERENCED_TABLE_NAME IS NOT NULL',
                            [$dbName, 'role_permissions']
                        );
                        foreach ($fks as $row) {
                            try {
                                DB::statement('ALTER TABLE `role_permissions` DROP FOREIGN KEY `' . $row->fk . '`');
                            } catch (\Throwable $e) {
                            }
                        }

                        // Supprimer l'index unique pivot si présent (souvent requis par la FK)
                        try {
                            DB::statement('ALTER TABLE `role_permissions` DROP INDEX `role_permissions_role_id_permission_id_unique`');
                        } catch (\Throwable $e) {
                        }
                    } else {
                        // PostgreSQL (ou autres): tenter l'ancien comportement
                        DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_role_id_foreign');
                        DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_permission_id_foreign');
                    }
                } catch (\Exception $e) {
                    // Ignorer si les contraintes n'existent pas
                }

                // Supprimer les colonnes pivot
                Schema::table('role_permissions', function (Blueprint $table) {
                    $cols = [];
                    if (Schema::hasColumn('role_permissions', 'role_id')) { $cols[] = 'role_id'; }
                    if (Schema::hasColumn('role_permissions', 'permission_id')) { $cols[] = 'permission_id'; }
                    if (!empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });

                // Ajouter les colonnes attendues (nullable d'abord pour éviter l'erreur)
                Schema::table('role_permissions', function (Blueprint $table) use ($hasRole, $hasPermissions, $hasEntrepriseId) {
                    if (!$hasRole) {
                        $table->string('role')->nullable()->after('id');
                    }
                    if (!$hasPermissions) {
                        $table->json('permissions')->nullable()->after('role');
                    }
                    if (!$hasEntrepriseId) {
                        // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED
                        $table->unsignedBigInteger('entreprise_id')->nullable()->after('role');
                    }
                });

                // Maintenant rendre role et permissions NOT NULL (la table est vide)
                try {
                    if ($driver === 'mysql') {
                        DB::statement('ALTER TABLE `role_permissions` MODIFY `role` VARCHAR(255) NOT NULL');
                        DB::statement('ALTER TABLE `role_permissions` MODIFY `permissions` JSON NOT NULL');
                    } else {
                        DB::statement('ALTER TABLE role_permissions ALTER COLUMN role SET NOT NULL');
                        DB::statement('ALTER TABLE role_permissions ALTER COLUMN permissions SET NOT NULL');
                    }
                } catch (\Throwable $e) {
                }

                // Ajouter les contraintes si entreprise_id existe et si la table entreprises existe
                if (Schema::hasColumn('role_permissions', 'entreprise_id') && Schema::hasTable('entreprises')) {
                    // Vérifier si la clé étrangère existe déjà
                    $fkExists = DB::select("
                        SELECT constraint_name
                        FROM information_schema.table_constraints
                        WHERE table_name = 'role_permissions'
                        AND constraint_type = 'FOREIGN KEY'
                        AND constraint_name LIKE '%entreprise_id%'
                    ");

                    if (empty($fkExists)) {
                        Schema::table('role_permissions', function (Blueprint $table) {
                            try {
                                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                            } catch (\Throwable $e) {}
                            try {
                                $table->index('entreprise_id');
                            } catch (\Throwable $e) {}
                        });
                    }
                }

                // Ajouter la contrainte d'unicité composite
                try {
                    DB::statement('ALTER TABLE role_permissions ADD CONSTRAINT role_permissions_role_entreprise_unique UNIQUE (role, entreprise_id)');
                } catch (\Exception $e) {
                    // La contrainte existe peut-être déjà
                }
            } else if (!$hasRole && !$hasPermissions) {
                // La table existe mais n'a pas les bonnes colonnes
                // Ajouter les colonnes manquantes
                Schema::table('role_permissions', function (Blueprint $table) use ($hasRole, $hasPermissions) {
                    if (!$hasRole) {
                        $table->string('role')->after('id');
                    }
                    if (!$hasPermissions) {
                        $table->json('permissions')->after('role');
                    }
                    if (!Schema::hasColumn('role_permissions', 'entreprise_id')) {
                        $table->unsignedBigInteger('entreprise_id')->nullable()->after('role');
                    }
                });

                // Ajouter les contraintes si nécessaire
                if (Schema::hasColumn('role_permissions', 'entreprise_id') && Schema::hasTable('entreprises')) {
                    Schema::table('role_permissions', function (Blueprint $table) {
                        try {
                            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                        } catch (\Throwable $e) {}
                        try {
                            $table->index('entreprise_id');
                        } catch (\Throwable $e) {}
                    });
                }
            }
        } else {
            // Créer la table si elle n'existe pas
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role');
                $table->json('permissions');
                $table->unsignedBigInteger('entreprise_id')->nullable();
                $table->timestamps();

                try { $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade'); } catch (\Throwable $e) {}
                try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
                $table->unique(['role', 'entreprise_id'], 'role_permissions_role_entreprise_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne rien faire en cas de rollback pour éviter de perdre des données
        // La structure pivot peut être restaurée manuellement si nécessaire
    }
};

