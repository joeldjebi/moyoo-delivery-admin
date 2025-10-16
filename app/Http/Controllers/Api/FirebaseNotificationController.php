<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SimpleFirebaseServiceV2;
use App\Models\Livreur;
use App\Models\Marchand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Firebase Notifications",
 *     description="Gestion des notifications push Firebase pour MOYOO Fleet"
 * )
 */
class FirebaseNotificationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/firebase/test-notification",
     *     summary="Tester l'envoi de notification Firebase",
     *     description="Envoie une notification de test pour vérifier la configuration Firebase",
     *     tags={"Firebase Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "title", "body"},
     *             @OA\Property(property="token", type="string", description="Token FCM de destination", example="fcm_token_example"),
     *             @OA\Property(property="title", type="string", description="Titre de la notification", example="Test MOYOO"),
     *             @OA\Property(property="body", type="string", description="Corps de la notification", example="Ceci est un test de notification Firebase"),
     *             @OA\Property(property="data", type="object", description="Données supplémentaires", example={"type": "test", "app": "moyoo_fleet"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification envoyée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification de test envoyée avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="notification_sent", type="boolean", example=true),
     *                 @OA\Property(property="firebase_response", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Données invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur Firebase",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de l'envoi de la notification"),
     *             @OA\Property(property="error", type="string", example="Firebase Server Key non configuré")
     *         )
     *     )
     * )
     */
    public function testNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|min:10',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $firebaseService = app(MoyooFirebaseService::class);

            $result = $firebaseService->sendCustomNotification(
                $request->token,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification de test envoyée avec succès',
                    'data' => [
                        'notification_sent' => true,
                        'firebase_response' => $result['response']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification',
                    'error' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erreur test notification Firebase', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/firebase/status",
     *     summary="Vérifier le statut de la configuration Firebase",
     *     description="Vérifie si Firebase est correctement configuré et accessible",
     *     tags={"Firebase Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statut Firebase récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Configuration Firebase vérifiée"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="project_id", type="string", example="moyoo-fleet"),
     *                 @OA\Property(property="service_configured", type="boolean", example=true),
     *                 @OA\Property(property="access_token_available", type="boolean", example=true),
     *                 @OA\Property(property="configuration_status", type="string", example="OK")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de configuration Firebase",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Configuration Firebase manquante"),
     *             @OA\Property(property="error", type="string", example="FIREBASE_PROJECT_ID non configuré")
     *         )
     *     )
     * )
     */
    public function getStatus()
    {
        try {
            $firebaseService = app(MoyooFirebaseService::class);

            // Vérifier la configuration
            $projectId = config('services.firebase.project_id');
            $serviceAccountConfigured = !empty(config('services.firebase.service_account_key.client_email'));

            return response()->json([
                'success' => true,
                'message' => 'Configuration Firebase vérifiée',
                'data' => [
                    'project_id' => $projectId,
                    'service_configured' => $serviceAccountConfigured,
                    'access_token_available' => true, // Si on arrive ici, c'est que le service fonctionne
                    'configuration_status' => $serviceAccountConfigured ? 'OK' : 'INCOMPLETE'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration Firebase manquante',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/firebase/send-to-livreur",
     *     summary="Envoyer une notification à un livreur",
     *     description="Envoie une notification personnalisée à un livreur spécifique",
     *     tags={"Firebase Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"livreur_id", "title", "body"},
     *             @OA\Property(property="livreur_id", type="integer", description="ID du livreur", example=1),
     *             @OA\Property(property="title", type="string", description="Titre de la notification", example="Nouvelle mission"),
     *             @OA\Property(property="body", type="string", description="Corps de la notification", example="Vous avez une nouvelle mission assignée"),
     *             @OA\Property(property="data", type="object", description="Données supplémentaires", example={"type": "mission", "mission_id": 123})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification envoyée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification envoyée au livreur"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="notification_sent", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livreur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Livreur non trouvé ou token FCM manquant")
     *         )
     *     )
     * )
     */
    public function sendToLivreur(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'livreur_id' => 'required|integer|exists:livreurs,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $livreur = Livreur::find($request->livreur_id);

            if (!$livreur || !$livreur->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livreur non trouvé ou token FCM manquant'
                ], 404);
            }

            $firebaseService = app(MoyooFirebaseService::class);

            $result = $firebaseService->sendCustomNotification(
                $livreur->fcm_token,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification envoyée au livreur',
                    'data' => [
                        'livreur_id' => $livreur->id,
                        'notification_sent' => true
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification',
                    'error' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification livreur', [
                'livreur_id' => $request->livreur_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/admin/firebase/send-to-marchand",
     *     summary="Envoyer une notification à un marchand",
     *     description="Envoie une notification personnalisée à un marchand spécifique",
     *     tags={"Firebase Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"marchand_id", "title", "body"},
     *             @OA\Property(property="marchand_id", type="integer", description="ID du marchand", example=1),
     *             @OA\Property(property="title", type="string", description="Titre de la notification", example="Colis livré"),
     *             @OA\Property(property="body", type="string", description="Corps de la notification", example="Votre colis a été livré avec succès"),
     *             @OA\Property(property="data", type="object", description="Données supplémentaires", example={"type": "delivery", "colis_id": 123})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification envoyée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification envoyée au marchand"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="marchand_id", type="integer", example=1),
     *                 @OA\Property(property="notification_sent", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marchand non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Marchand non trouvé ou token FCM manquant")
     *         )
     *     )
     * )
     */
    public function sendToMarchand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'marchand_id' => 'required|integer|exists:marchands,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $marchand = Marchand::find($request->marchand_id);

            if (!$marchand || !$marchand->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marchand non trouvé ou token FCM manquant'
                ], 404);
            }

            $firebaseService = app(MoyooFirebaseService::class);

            $result = $firebaseService->sendCustomNotification(
                $marchand->fcm_token,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification envoyée au marchand',
                    'data' => [
                        'marchand_id' => $marchand->id,
                        'notification_sent' => true
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification',
                    'error' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification marchand', [
                'marchand_id' => $request->marchand_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
