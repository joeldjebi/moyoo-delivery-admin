<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin a accès à tout
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Vérifier la permission spécifique
        if (!$user->hasPermission($permission)) {
            throw new AuthorizationException("Vous n'avez pas les permissions pour accéder à cette ressource.");
        }

        return $next($request);
    }
}
