<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SwaggerController extends Controller
{
    /**
     * @OA\Info(
     *     title="MOYOO Delivery API",
     *     version="1.0.0",
     *     description="API pour le système de livraison MOYOO avec géolocalisation en temps réel"
     * )
     *
     * @OA\Server(
     *     url="http://192.168.1.8:8000",
     *     description="Serveur de développement MOYOO"
     * )
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     description="Authentification JWT pour les livreurs"
     * )
     *
     * @OA\Tag(
     *     name="Authentification",
     *     description="Endpoints d'authentification des livreurs"
     * )
     *
     * @OA\Tag(
     *     name="Géolocalisation",
     *     description="Endpoints de géolocalisation en temps réel"
     * )
     *
     * @OA\Tag(
     *     name="Livreur",
     *     description="Endpoints spécifiques aux livreurs"
     * )
     *
     * @OA\Tag(
     *     name="Admin",
     *     description="Endpoints d'administration"
     * )
     */
    public function test()
    {
        return response()->json([
            'message' => 'API MOYOO Delivery fonctionnelle',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString()
        ]);
    }
}
