<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            \Log::warning('TenantMiddleware: Utilisateur non authentifié', [
                'ip' => $request->ip(),
                'url' => $request->url()
            ]);
            return redirect()->route('login');
        }

        // Super admin peut accéder à tout
        if ($user->isSuperAdmin()) {
            \Log::info('TenantMiddleware: Accès super admin autorisé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'url' => $request->url()
            ]);
            return $next($request);
        }

        // Vérifier que l'utilisateur a une entreprise
        if (!$user->entreprise_id) {
            \Log::warning('TenantMiddleware: Utilisateur sans entreprise - Déconnexion', [
                'user_id' => $user->id,
                'email' => $user->email,
                'entreprise_id' => $user->entreprise_id,
                'ip' => $request->ip(),
                'url' => $request->url()
            ]);
            Auth::logout();
            return redirect()->route('login')->with('error', 'Aucune entreprise associée à votre compte.');
        }

        // Ajouter l'entreprise_id à la requête pour les scopes
        $request->merge(['current_entreprise_id' => $user->entreprise_id]);

        \Log::info('TenantMiddleware: Accès autorisé', [
            'user_id' => $user->id,
            'email' => $user->email,
            'entreprise_id' => $user->entreprise_id,
            'ip' => $request->ip(),
            'url' => $request->url()
        ]);

        return $next($request);
    }
}
