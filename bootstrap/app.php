<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'jwt.livreur' => \App\Http\Middleware\JwtLivreurMiddleware::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'module' => \App\Http\Middleware\CheckModuleAccess::class,
            'entreprise.status' => \App\Http\Middleware\CheckEntrepriseStatus::class,
        ]);
        
        // Appliquer le middleware CheckEntrepriseStatus à toutes les routes web authentifiées
        $middleware->web(append: [
            \App\Http\Middleware\CheckEntrepriseStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Accès refusé',
                    'message' => 'Vous n\'avez pas les permissions pour accéder à cette ressource.',
                    'code' => 403
                ], 403);
            }

            // Page d'erreur 403 simple
            return response()->view('errors.403', [
                'exception' => $e,
                'title' => 'Accès Refusé',
                'menu' => 'error'
            ], 403);
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Page non trouvée',
                    'message' => 'La ressource demandée n\'existe pas.',
                    'code' => 404
                ], 404);
            }

            // Page d'erreur 404 simple
            return response()->view('errors.404', [
                'exception' => $e,
                'title' => 'Page Non Trouvée',
                'menu' => 'error'
            ], 404);
        });
    })->create();
