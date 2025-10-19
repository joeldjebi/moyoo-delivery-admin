<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use App\Models\Marchand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="FCM Token",
 *     description="Gestion des tokens FCM pour les notifications push"
 * )
 */
class FcmTokenController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/fcm-token",
     *     summary="Mettre à jour le token FCM de l'utilisateur connecté (Admin/User)",
     *     tags={"FCM Token"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fcm_token"},
     *             @OA\Property(property="fcm_token", type="string", description="Token FCM de l'utilisateur"),
     *             @OA\Property(property="device_type", type="string", description="Type d'appareil (android/ios)", example="android")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token FCM mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="fcm_token", type="string", example="fcm_token_example"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|min:10',
            'device_type' => 'nullable|string|in:android,ios,web'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Essayer d'abord l'authentification web, puis sanctum
            $user = auth()->user() ?? auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Mettre à jour le token FCM
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);

            Log::info('Token FCM mis à jour pour l\'utilisateur', [
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'device_type' => $request->device_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM mis à jour avec succès',
                'data' => [
                    'user_id' => $user->id,
                    'user_type' => $user->user_type,
                    'fcm_token' => $user->fcm_token,
                    'updated_at' => $user->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour token FCM utilisateur', [
                'user_id' => auth('sanctum')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du token FCM'
            ], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/livreur/fcm-token",
     *     summary="Mettre à jour le token FCM d'un livreur",
     *     tags={"FCM Token"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fcm_token"},
     *             @OA\Property(property="fcm_token", type="string", description="Token FCM du livreur"),
     *             @OA\Property(property="device_type", type="string", description="Type d'appareil (android/ios)", example="android")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token FCM mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="fcm_token", type="string", example="fcm_token_example"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
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
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token d'authentification invalide")
     *         )
     *     )
     * )
     */
    public function updateLivreurToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|min:10',
            'device_type' => 'nullable|string|in:android,ios,web'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $livreur = auth('livreur')->user();

            if (!$livreur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livreur non trouvé'
                ], 404);
            }

            // Mettre à jour le token FCM
            $livreur->update([
                'fcm_token' => $request->fcm_token,
                'fcm_token_updated_at' => now()
            ]);

            Log::info('Token FCM mis à jour pour le livreur', [
                'livreur_id' => $livreur->id,
                'device_type' => $request->device_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM mis à jour avec succès',
                'data' => [
                    'livreur_id' => $livreur->id,
                    'fcm_token' => $livreur->fcm_token,
                    'updated_at' => $livreur->fcm_token_updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour token FCM livreur', [
                'livreur_id' => auth('livreur')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du token FCM'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/marchand/fcm-token",
     *     summary="Mettre à jour le token FCM d'un marchand",
     *     tags={"FCM Token"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fcm_token"},
     *             @OA\Property(property="fcm_token", type="string", description="Token FCM du marchand"),
     *             @OA\Property(property="device_type", type="string", description="Type d'appareil (android/ios)", example="android")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token FCM mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="marchand_id", type="integer", example=1),
     *                 @OA\Property(property="fcm_token", type="string", example="fcm_token_example"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     )
     * )
     */
    public function updateMarchandToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|min:10',
            'device_type' => 'nullable|string|in:android,ios,web'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $marchand = auth('marchand')->user();

            if (!$marchand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marchand non trouvé'
                ], 404);
            }

            // Mettre à jour le token FCM
            $marchand->update([
                'fcm_token' => $request->fcm_token,
                'fcm_token_updated_at' => now()
            ]);

            Log::info('Token FCM mis à jour pour le marchand', [
                'marchand_id' => $marchand->id,
                'device_type' => $request->device_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM mis à jour avec succès',
                'data' => [
                    'marchand_id' => $marchand->id,
                    'fcm_token' => $marchand->fcm_token,
                    'updated_at' => $marchand->fcm_token_updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour token FCM marchand', [
                'marchand_id' => auth('marchand')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du token FCM'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/livreur/fcm-token",
     *     summary="Supprimer le token FCM d'un livreur",
     *     tags={"FCM Token"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token FCM supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function deleteLivreurToken()
    {
        try {
            $livreur = auth('livreur')->user();

            if (!$livreur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livreur non trouvé'
                ], 404);
            }

            $livreur->update([
                'fcm_token' => null,
                'fcm_token_updated_at' => null
            ]);

            Log::info('Token FCM supprimé pour le livreur', [
                'livreur_id' => $livreur->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression token FCM livreur', [
                'livreur_id' => auth('livreur')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du token FCM'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/marchand/fcm-token",
     *     summary="Supprimer le token FCM d'un marchand",
     *     tags={"FCM Token"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token FCM supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function deleteMarchandToken()
    {
        try {
            $marchand = auth('marchand')->user();

            if (!$marchand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marchand non trouvé'
                ], 404);
            }

            $marchand->update([
                'fcm_token' => null,
                'fcm_token_updated_at' => null
            ]);

            Log::info('Token FCM supprimé pour le marchand', [
                'marchand_id' => $marchand->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression token FCM marchand', [
                'marchand_id' => auth('marchand')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du token FCM'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/fcm-token",
     *     summary="Supprimer le token FCM de l'utilisateur connecté",
     *     tags={"FCM Token"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token FCM supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token FCM supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function destroy()
    {
        try {
            // Essayer d'abord l'authentification web, puis sanctum
            $user = auth()->user() ?? auth('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Supprimer le token FCM
            $user->update([
                'fcm_token' => null
            ]);

            Log::info('Token FCM supprimé pour l\'utilisateur', [
                'user_id' => $user->id,
                'user_type' => $user->user_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression token FCM utilisateur', [
                'user_id' => auth('sanctum')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du token FCM'
            ], 500);
        }
    }
}
