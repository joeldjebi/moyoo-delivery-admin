<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="API MOYOO Fleet - Delivery & Notifications",
 *     version="2.0.0",
 *     description="API complète pour l'application MOYOO Fleet incluant la gestion des livraisons, ramassages et notifications push Firebase",
 *     @OA\Contact(
 *         email="support@moyoo.com",
 *         name="Support MOYOO"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://192.168.1.5:8000",
 *     description="Serveur de développement"
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
 *     name="Profil",
 *     description="Gestion du profil du livreur"
 * )
 *
 * @OA\Tag(
 *     name="Colis",
 *     description="Gestion des colis et livraisons"
 * )
 *
 * @OA\Tag(
 *     name="Géolocalisation",
 *     description="Mise à jour de la position du livreur"
 * )
 *
 * @OA\Tag(
 *     name="Statistiques",
 *     description="Statistiques et rapports du livreur"
 * )
 *
 * @OA\Tag(
 *     name="FCM Token",
 *     description="Gestion des tokens FCM pour les notifications push"
 * )
 *
 * @OA\Tag(
 *     name="Firebase Notifications",
 *     description="Gestion des notifications push Firebase pour MOYOO Fleet"
 * )
 *
 * @OA\Tag(
 *     name="Test",
 *     description="Endpoints de test et vérification"
 * )
 */
class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Test de l'API",
     *     description="Endpoint de test pour vérifier que l'API fonctionne",
     *     tags={"Test"},
     *     @OA\Response(
     *         response=200,
     *         description="API fonctionnelle",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="API fonctionnelle"),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'API fonctionnelle',
            'timestamp' => now()
        ]);
    }
}
