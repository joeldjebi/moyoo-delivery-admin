<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'permissions',
        'entreprise_id'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    /**
     * Obtenir les permissions pour un rôle donné
     */
    public static function getPermissionsForRole($role, $entrepriseId = null)
    {
        $entrepriseId = $entrepriseId ?? (auth()->user()->entreprise_id ?? null);

        // Chercher d'abord avec l'entreprise_id spécifié
        $query = self::where('role', $role);
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        } else {
            // Si pas d'entreprise_id, chercher les permissions globales (null)
            $query->whereNull('entreprise_id');
        }

        $rolePermission = $query->first();

        // Si rien trouvé avec l'entreprise_id spécifié, chercher les permissions globales
        if (!$rolePermission && $entrepriseId) {
            $rolePermission = self::where('role', $role)
                ->whereNull('entreprise_id')
                ->first();
        }

        return $rolePermission ? ($rolePermission->permissions ?? []) : [];
    }

    /**
     * Mettre à jour les permissions pour un rôle
     */
    public static function updatePermissionsForRole($role, $permissions)
    {
        return self::updateOrCreate(
            ['role' => $role],
            ['permissions' => $permissions]
        );
    }

    /**
     * Obtenir tous les rôles avec leurs permissions
     */
    public static function getAllRolePermissions()
    {
        return self::all()->pluck('permissions', 'role')->toArray();
    }

    /**
     * Scope: filtrer par entreprise
     */
    public function scopeByEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }
}
