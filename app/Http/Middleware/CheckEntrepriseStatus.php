<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Entreprise;
use Symfony\Component\HttpFoundation\Response;

class CheckEntrepriseStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exclure les routes d'authentification et d'inscription
        $excludedRoutes = [
            'auth.login',
            'auth.register',
            'auth.showLogin',
            'auth.showRegister',
            'auth.loginUser',
            'auth.registerUser',
            'auth.verify-otp',
            'auth.verifyOtp',
            'entreprise.create',
            'entreprise.store',
        ];

        $routeName = $request->route()?->getName();
        if (in_array($routeName, $excludedRoutes)) {
            return $next($request);
        }

        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Les super admins ne sont pas concernés par cette vérification
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Récupérer l'entreprise de l'utilisateur
        // Essayer d'abord avec entreprise_id, puis avec created_by
        $entreprise = null;
        if ($user->entreprise_id) {
            $entreprise = Entreprise::find($user->entreprise_id);
        }
        
        // Si pas d'entreprise via entreprise_id, essayer avec created_by
        if (!$entreprise) {
            $entreprise = Entreprise::getEntrepriseByUser($user->id);
        }

        // Si l'utilisateur a une entreprise, vérifier son statut
        if ($entreprise) {
            // Vérifier si l'entreprise est active (statut = 1)
            if ((int)$entreprise->statut !== 1) {
                Log::warning('Accès refusé - Entreprise inactive', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'entreprise_id' => $entreprise->id,
                    'statut' => $entreprise->statut,
                    'route' => $request->route()?->getName(),
                    'url' => $request->url()
                ]);

                // Déconnecter l'utilisateur
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Rediriger vers la page de connexion avec un message
                return redirect()->route('auth.login')
                    ->withErrors([
                        'email' => 'Votre compte entreprise est inactif. Veuillez contacter l\'administrateur pour plus d\'informations.'
                    ]);
            }
        }

        return $next($request);
    }
}
