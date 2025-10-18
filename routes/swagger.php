<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Route pour afficher la documentation Swagger
Route::get('/api/documentation', function () {
    $docsPath = storage_path('api-docs/api-docs.json');

    if (!file_exists($docsPath)) {
        return response()->json([
            'error' => 'Documentation Swagger non trouvée',
            'message' => 'Veuillez générer la documentation avec: php artisan l5-swagger:generate'
        ], 404);
    }

    $docs = json_decode(file_get_contents($docsPath), true);

    return view('swagger-ui', [
        'docs' => $docs,
        'title' => 'MOYOO Admin Delivery API Documentation'
    ]);
});

// Route pour servir le fichier JSON de documentation
Route::get('/api-docs.json', function () {
    $docsPath = storage_path('api-docs/api-docs.json');

    if (!file_exists($docsPath)) {
        return response()->json([
            'error' => 'Documentation Swagger non trouvée'
        ], 404);
    }

    return response()->file($docsPath);
});

// Route pour servir les assets Swagger UI
Route::get('/swagger-ui/{file}', function ($file) {
    $assetPath = public_path("vendor/swagger-ui/{$file}");

    if (file_exists($assetPath)) {
        return response()->file($assetPath);
    }

    return response()->json(['error' => 'Asset non trouvé'], 404);
});
