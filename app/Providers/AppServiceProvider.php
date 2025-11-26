<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Utiliser Bootstrap 5 pour la pagination
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Forcer HTTPS pour ngrok et les environnements utilisant HTTPS
        // Vérifier si on est derrière ngrok ou si la requête est en HTTPS
        $isHttps = request()->secure() 
            || request()->header('x-forwarded-proto') === 'https' 
            || str_contains(request()->getHost(), 'ngrok')
            || str_contains(request()->url(), 'ngrok');
            
        if ($isHttps) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
