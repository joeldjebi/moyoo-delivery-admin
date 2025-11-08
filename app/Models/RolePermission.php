<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'permission_id',
        'entreprise_id'
    ];

    /**
     * Obtenir le role_id à partir du nom du rôle
     */
    protected static function getRoleIdByName($roleName)
    {
        $role = DB::table('roles')
            ->where('name', $roleName)
            ->orWhere('slug', $roleName)
            ->first();

        return $role ? $role->id : null;
    }

    /**
     * Obtenir les permissions pour un rôle donné
     */
    public static function getPermissionsForRole($role, $entrepriseId = null)
    {
        $entrepriseId = $entrepriseId ?? (auth()->user()->entreprise_id ?? null);

        // Obtenir le role_id à partir du nom du rôle
        $roleId = self::getRoleIdByName($role);

        if (!$roleId) {
            return [];
        }

        // Chercher d'abord avec l'entreprise_id spécifié
        $query = self::where('role_id', $roleId);
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        } else {
            // Si pas d'entreprise_id, chercher les permissions globales (null)
            $query->whereNull('entreprise_id');
        }

        $rolePermissions = $query->pluck('permission_id')->toArray();

        // Si rien trouvé avec l'entreprise_id spécifié, chercher les permissions globales
        if (empty($rolePermissions) && $entrepriseId) {
            $rolePermissions = self::where('role_id', $roleId)
                ->whereNull('entreprise_id')
                ->pluck('permission_id')
                ->toArray();
        }

        // Récupérer les noms des permissions
        if (empty($rolePermissions)) {
            return [];
        }

        $permissions = DB::table('permissions')
            ->whereIn('id', $rolePermissions)
            ->get()
            ->map(function ($permission) {
                return $permission->name ?? ($permission->resource . '.' . $permission->action);
            })
            ->toArray();

        return $permissions;
    }

    /**
     * Mettre à jour les permissions pour un rôle
     */
    public static function updatePermissionsForRole($role, $permissions)
    {
        // Obtenir le role_id à partir du nom du rôle
        $roleId = self::getRoleIdByName($role);

        if (!$roleId) {
            throw new \Exception("Le rôle '{$role}' n'existe pas");
        }

        // Si $permissions est un array de noms, convertir en IDs
        if (is_array($permissions) && !empty($permissions) && !is_numeric($permissions[0])) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', $permissions)
                ->pluck('id')
                ->toArray();
        } else {
            $permissionIds = $permissions;
        }

        // Supprimer les permissions existantes pour ce rôle
        self::where('role_id', $roleId)->delete();

        // Ajouter les nouvelles permissions
        foreach ($permissionIds as $permissionId) {
            self::create([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'entreprise_id' => null
            ]);
        }

        return true;
    }

    /**
     * Obtenir tous les rôles avec leurs permissions
     */
    public static function getAllRolePermissions()
    {
        $result = [];

        $roles = DB::table('roles')->get();

        foreach ($roles as $role) {
            $permissionIds = self::where('role_id', $role->id)
                ->pluck('permission_id')
                ->toArray();

            if (!empty($permissionIds)) {
                $permissions = DB::table('permissions')
                    ->whereIn('id', $permissionIds)
                    ->get()
                    ->map(function ($permission) {
                        return $permission->name ?? ($permission->resource . '.' . $permission->action);
                    })
                    ->toArray();

                $result[$role->name] = $permissions;
            } else {
                $result[$role->name] = [];
            }
        }

        return $result;
    }

    /**
     * Scope: filtrer par entreprise
     */
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }
}
