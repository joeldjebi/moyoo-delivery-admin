<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

        // Normaliser le nom du rôle (peut être un nom ou un slug)
        $roleName = is_numeric($role) ? self::getRoleNameById($role) : $role;

        if (!$roleName) {
            return [];
        }

        // Chercher d'abord avec l'entreprise_id spécifié
        $query = self::where('role', $roleName);
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        } else {
            // Si pas d'entreprise_id, chercher les permissions globales (null)
            $query->whereNull('entreprise_id');
        }

        $rolePermission = $query->first();

        // Si rien trouvé avec l'entreprise_id spécifié, chercher les permissions globales
        if (!$rolePermission && $entrepriseId) {
            $rolePermission = self::where('role', $roleName)
                ->whereNull('entreprise_id')
                ->first();
        }

        if (!$rolePermission || empty($rolePermission->permissions)) {
            return [];
        }

        // Les permissions sont stockées en JSON, les retourner directement
        return is_array($rolePermission->permissions)
            ? $rolePermission->permissions
            : json_decode($rolePermission->permissions, true) ?? [];
    }

    /**
     * Obtenir le nom du rôle à partir de son ID
     */
    protected static function getRoleNameById($roleId)
    {
        $role = DB::table('roles')->where('id', $roleId)->first();
        return $role ? ($role->name ?? $role->slug) : null;
    }

    /**
     * Mettre à jour les permissions pour un rôle
     */
    public static function updatePermissionsForRole($role, $permissions, $entrepriseId = null)
    {
        // Normaliser le nom du rôle
        $roleName = is_numeric($role) ? self::getRoleNameById($role) : $role;

        if (!$roleName) {
            throw new \Exception("Le rôle '{$role}' n'existe pas");
        }

        // S'assurer que $permissions est un tableau
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        // Chercher ou créer l'entrée pour ce rôle et cette entreprise
        $rolePermission = self::firstOrNew([
            'role' => $roleName,
            'entreprise_id' => $entrepriseId
        ]);

        $rolePermission->permissions = $permissions;
        $rolePermission->save();

        return true;
    }

    /**
     * Obtenir tous les rôles avec leurs permissions
     */
    public static function getAllRolePermissions($entrepriseId = null)
    {
        $result = [];

        // Récupérer tous les role_permissions
        $query = self::query();
        if ($entrepriseId) {
            // D'abord récupérer les permissions spécifiques à l'entreprise
            $entreprisePermissions = self::where('entreprise_id', $entrepriseId)->get();

            // Ensuite récupérer les permissions globales pour les rôles qui n'ont pas de permissions spécifiques
            $rolesWithEnterprisePermissions = $entreprisePermissions->pluck('role')->toArray();
            $globalPermissions = self::whereNull('entreprise_id')
                ->whereNotIn('role', $rolesWithEnterprisePermissions)
                ->get();

            $rolePermissions = $entreprisePermissions->merge($globalPermissions);
        } else {
            $rolePermissions = self::whereNull('entreprise_id')->get();
        }

        foreach ($rolePermissions as $rolePermission) {
            $roleName = $rolePermission->role;
            $permissions = is_array($rolePermission->permissions)
                ? $rolePermission->permissions
                : json_decode($rolePermission->permissions, true) ?? [];

            // Prioriser les permissions spécifiques à l'entreprise
            // Si le rôle existe déjà (permission globale), la remplacer par la permission spécifique
            if (!isset($result[$roleName]) || $rolePermission->entreprise_id !== null) {
                $result[$roleName] = $permissions;
            }
        }

        // S'assurer que tous les rôles de la table roles sont présents
        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            if (!isset($result[$role->name])) {
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
