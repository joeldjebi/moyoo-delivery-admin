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
        $query = self::where('role', $role);
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        }
        $rolePermission = $query->first();
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
