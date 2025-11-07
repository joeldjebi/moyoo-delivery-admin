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

        if ($tableExists) {
            // Vérifier si la table a la structure pivot (role_id, permission_id)
            $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
            $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');
            $hasRole = Schema::hasColumn('role_permissions', 'role');
            $hasPermissions = Schema::hasColumn('role_permissions', 'permissions');

            // Si la table a la structure pivot, la transformer
            if ($hasRoleId && $hasPermissionId && !$hasRole && !$hasPermissions) {
                // Supprimer les données existantes (structure pivot incompatible)
                DB::table('role_permissions')->truncate();

                // Supprimer les contraintes existantes
                try {
                    DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_role_id_foreign');
                    DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_permission_id_foreign');
                } catch (\Exception $e) {
                    // Ignorer si les contraintes n'existent pas
                }

                // Supprimer les colonnes pivot
                Schema::table('role_permissions', function (Blueprint $table) {
                    $table->dropColumn(['role_id', 'permission_id']);
                });

                // Ajouter les colonnes attendues (nullable d'abord pour éviter l'erreur)
                Schema::table('role_permissions', function (Blueprint $table) {
                    $table->string('role')->nullable()->after('id');
                    $table->json('permissions')->nullable()->after('role');
                    $table->bigInteger('entreprise_id')->nullable()->after('role');
                });

                // Maintenant rendre role et permissions NOT NULL (la table est vide)
                DB::statement('ALTER TABLE role_permissions ALTER COLUMN role SET NOT NULL');
                DB::statement('ALTER TABLE role_permissions ALTER COLUMN permissions SET NOT NULL');

                // Ajouter les contraintes
                Schema::table('role_permissions', function (Blueprint $table) {
                    $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                    $table->index('entreprise_id');
                });

                // Ajouter la contrainte d'unicité composite
                try {
                    DB::statement('ALTER TABLE role_permissions ADD CONSTRAINT role_permissions_role_entreprise_unique UNIQUE (role, entreprise_id)');
                } catch (\Exception $e) {
                    // La contrainte existe peut-être déjà
                }
            } else if (!$hasRole && !$hasPermissions) {
                // La table existe mais n'a pas les bonnes colonnes
                // Ajouter les colonnes manquantes
                Schema::table('role_permissions', function (Blueprint $table) use ($hasRole, $hasPermissions, $hasRoleId, $hasPermissionId) {
                    if (!$hasRole) {
                        $table->string('role')->after('id');
                    }
                    if (!$hasPermissions) {
                        $table->json('permissions')->after('role');
                    }
                    if (!Schema::hasColumn('role_permissions', 'entreprise_id')) {
                        $table->bigInteger('entreprise_id')->nullable()->after('role');
                    }
                });

                // Ajouter les contraintes si nécessaire
                if (!Schema::hasColumn('role_permissions', 'entreprise_id')) {
                    Schema::table('role_permissions', function (Blueprint $table) {
                        $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                        $table->index('entreprise_id');
                    });
                }
            }
        } else {
            // Créer la table si elle n'existe pas
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role');
                $table->json('permissions');
                $table->bigInteger('entreprise_id')->nullable();
                $table->timestamps();

                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                $table->index('entreprise_id');
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

