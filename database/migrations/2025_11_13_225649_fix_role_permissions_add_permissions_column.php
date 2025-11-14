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
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        // Vérifier si la colonne permission_id existe (structure pivot)
        $hasPermissionId = Schema::hasColumn('role_permissions', 'permission_id');
        $hasPermissions = Schema::hasColumn('role_permissions', 'permissions');

        if ($hasPermissionId && !$hasPermissions) {
            // Migrer les données de la structure pivot vers JSON
            // D'abord, créer une table temporaire pour stocker les données groupées
            DB::statement('
                CREATE TEMP TABLE temp_role_permissions AS
                SELECT 
                    role,
                    entreprise_id,
                    array_agg(DISTINCT permission_id ORDER BY permission_id) as permission_ids
                FROM role_permissions
                GROUP BY role, entreprise_id
            ');

            // Récupérer les données groupées
            $groupedPermissions = DB::select('SELECT * FROM temp_role_permissions');

            // Supprimer les contraintes de clé étrangère si elles existent
            try {
                DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_permission_id_foreign');
            } catch (\Exception $e) {
                // Ignorer si la contrainte n'existe pas
            }

            // Supprimer toutes les lignes existantes (on va les recréer avec la nouvelle structure)
            DB::table('role_permissions')->truncate();

            // Supprimer la colonne permission_id
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->dropColumn('permission_id');
            });

            // Ajouter la colonne permissions (JSON)
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->json('permissions')->nullable()->after('role');
            });

            // Remplir la colonne permissions avec les données migrées
            foreach ($groupedPermissions as $group) {
                // Convertir le tableau PostgreSQL en tableau PHP
                $permissionIds = [];
                if ($group->permission_ids) {
                    if (is_array($group->permission_ids)) {
                        $permissionIds = $group->permission_ids;
                    } elseif (is_string($group->permission_ids)) {
                        // PostgreSQL retourne les tableaux comme des chaînes "{1,2,3}"
                        $permissionIds = str_replace(['{', '}'], '', $group->permission_ids);
                        $permissionIds = $permissionIds ? explode(',', $permissionIds) : [];
                        $permissionIds = array_map('intval', array_filter($permissionIds));
                    }
                }

                // Si vous avez une table permissions, récupérer les noms des permissions
                // Sinon, utiliser les IDs directement
                $permissions = [];
                if (!empty($permissionIds) && Schema::hasTable('permissions')) {
                    $permissionNames = DB::table('permissions')
                        ->whereIn('id', $permissionIds)
                        ->pluck('name')
                        ->toArray();
                    $permissions = $permissionNames;
                } elseif (!empty($permissionIds)) {
                    // Utiliser les IDs comme permissions si pas de table permissions
                    $permissions = array_map('strval', $permissionIds);
                }

                // Insérer la nouvelle ligne avec la structure JSON
                DB::table('role_permissions')->insert([
                    'role' => $group->role,
                    'entreprise_id' => $group->entreprise_id,
                    'permissions' => json_encode($permissions),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Rendre la colonne permissions NOT NULL après migration
            DB::statement('ALTER TABLE role_permissions ALTER COLUMN permissions SET NOT NULL');
            DB::statement('ALTER TABLE role_permissions ALTER COLUMN permissions SET DEFAULT \'[]\'::json');

        } elseif (!$hasPermissions) {
            // La colonne permissions n'existe pas mais permission_id non plus
            // Ajouter simplement la colonne permissions
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->json('permissions')->default('[]')->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        // Ne pas faire de rollback automatique pour éviter de perdre des données
        // La structure pivot peut être restaurée manuellement si nécessaire
    }
};
