<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ModuleAccessService;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    protected $moduleAccessService;

    public function __construct(ModuleAccessService $moduleAccessService)
    {
        $this->moduleAccessService = $moduleAccessService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        $user = auth()->user();

        if (!$user || !$user->entreprise_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé.');
        }

        // Le dashboard est toujours accessible
        if ($moduleSlug === 'dashboard') {
            return $next($request);
        }

        if (!$this->moduleAccessService->hasAccess($user->entreprise_id, $moduleSlug)) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Ce module n\'est pas disponible dans votre plan actuel. Veuillez passer à un plan supérieur pour accéder à cette fonctionnalité.');
        }

        return $next($request);
    }
}

