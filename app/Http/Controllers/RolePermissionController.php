<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RolePermissionController extends Controller
{
    /**
     * Afficher la page de gestion des permissions des rôles
     */
    public function index()
    {
        $data['title'] = 'Gestion des Permissions des Rôles';
        $data['menu'] = 'role-permissions';

        // Vérifier les permissions
        if (!Auth::user()->hasPermission('settings.update')) {
            abort(403, 'Vous n\'avez pas les permissions pour gérer les permissions des rôles.');
        }

        // Récupérer toutes les permissions disponibles
        $data['availablePermissions'] = User::getAllAvailablePermissions();

        // Récupérer les permissions actuelles des rôles pour l'entreprise de l'utilisateur
        $entrepriseId = Auth::user()->entreprise_id ?? null;
        $data['rolePermissions'] = RolePermission::getAllRolePermissions($entrepriseId);

        // Rôles disponibles
        $data['roles'] = ['admin', 'manager', 'user'];

        return view('role-permissions.index', $data);
    }

    /**
     * Mettre à jour les permissions d'un rôle
     */
    public function update(Request $request)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasPermission('settings.update')) {
            abort(403, 'Vous n\'avez pas les permissions pour modifier les permissions des rôles.');
        }

        $request->validate([
            'role' => 'required|in:admin,manager,user',
            'permissions' => 'array',
            'permissions.*' => 'string'
        ]);

        $role = $request->role;
        $permissions = $request->permissions ?? [];
        $entrepriseId = Auth::user()->entreprise_id ?? null;

        // Debug: logger les données reçues
        \Log::info('Mise à jour des permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'entreprise_id' => $entrepriseId,
            'count' => count($permissions)
        ]);

        // Mettre à jour les permissions du rôle
        RolePermission::updatePermissionsForRole($role, $permissions, $entrepriseId);

        return redirect()->route('role-permissions.index')
            ->with('success', "Les permissions du rôle '{$role}' ont été mises à jour avec succès.");
    }

    /**
     * Obtenir les permissions d'un rôle (AJAX)
     */
    public function getRolePermissions(Request $request)
    {
        $role = $request->role;
        $permissions = RolePermission::getPermissionsForRole($role);

        return response()->json([
            'permissions' => $permissions
        ]);
    }
}
