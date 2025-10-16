<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ErrorController extends Controller
{
    /**
     * Afficher la page d'erreur 403
     */
    public function show403(Request $request)
    {
        $data['title'] = 'Accès Refusé';
        $data['menu'] = 'error';

        // Log de l'erreur 403
        Log::warning('Erreur 403 - Accès refusé', [
            'user_id' => Auth::id(),
            'user_email' => Auth::check() ? Auth::user()->email : 'Non authentifié',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer')
        ]);

        return response()->view('errors.403', $data, 403);
    }

    /**
     * Afficher la page d'erreur 404
     */
    public function show404(Request $request)
    {
        $data['title'] = 'Page Non Trouvée';
        $data['menu'] = 'error';

        // Log de l'erreur 404
        Log::info('Erreur 404 - Page non trouvée', [
            'user_id' => Auth::id(),
            'user_email' => Auth::check() ? Auth::user()->email : 'Non authentifié',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer')
        ]);

        return response()->view('errors.404', $data, 404);
    }

    /**
     * Afficher la page d'erreur 500
     */
    public function show500(Request $request)
    {
        $data['title'] = 'Erreur Serveur';
        $data['menu'] = 'error';

        // Log de l'erreur 500
        Log::error('Erreur 500 - Erreur serveur', [
            'user_id' => Auth::id(),
            'user_email' => Auth::check() ? Auth::user()->email : 'Non authentifié',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer')
        ]);

        return response()->view('errors.500', $data, 500);
    }

    /**
     * API pour obtenir les informations de l'utilisateur connecté
     */
    public function getUserInfo(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'user_type' => $user->user_type,
                'status' => $user->status,
                'permissions' => $user->permissions ?? [],
                'entreprise' => $user->entreprise ? [
                    'id' => $user->entreprise->id,
                    'name' => $user->entreprise->name
                ] : null
            ],
            'permissions' => [
                'role_permissions' => \App\Models\RolePermission::getPermissionsForRole($user->role),
                'custom_permissions' => $user->permissions ?? [],
                'all_permissions' => array_merge(
                    \App\Models\RolePermission::getPermissionsForRole($user->role),
                    $user->permissions ?? []
                )
            ]
        ]);
    }
}
