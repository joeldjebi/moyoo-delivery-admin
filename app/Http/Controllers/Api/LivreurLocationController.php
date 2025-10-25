<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LivreurLocation;
use App\Models\LivreurLocationStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LivreurLocationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/livreur/location/update",
     *     summary="Mettre à jour la position du livreur avec contexte de mission",
     *     description="Met à jour la position géographique du livreur avec les données GPS et le contexte de mission (ramassage/livraison)",
     *     tags={"Géolocalisation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude", "longitude"},
     *             @OA\Property(property="livreur_id", type="integer", example=1, description="ID du livreur"),
     *             @OA\Property(property="entreprise_id", type="integer", example=1, description="ID de l'entreprise (automatique)"),
     *             @OA\Property(property="latitude", type="number", format="float", example=5.316667, description="Latitude"),
     *             @OA\Property(property="longitude", type="number", format="float", example=-4.033333, description="Longitude"),
     *             @OA\Property(property="accuracy", type="number", format="float", example=10.5, description="Précision en mètres"),
     *             @OA\Property(property="altitude", type="number", format="float", example=50.0, description="Altitude en mètres"),
     *             @OA\Property(property="speed", type="number", format="float", example=15.5, description="Vitesse en m/s"),
     *             @OA\Property(property="heading", type="number", format="float", example=180.0, description="Direction en degrés"),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z", description="Timestamp de la position"),
     *             @OA\Property(property="status", type="string", enum={"en_cours", "en_pause", "termine"}, example="en_cours", description="Statut de livraison"),
     *             @OA\Property(property="context_type", type="string", enum={"ramassage", "livraison", "libre"}, example="ramassage", description="Type de mission en cours"),
     *             @OA\Property(property="context_id", type="integer", example=1, description="ID de la mission en cours (ramassage_id ou historique_livraison_id)"),
     *             @OA\Property(property="ramassage_id", type="integer", example=1, description="ID du ramassage en cours (optionnel, déprécié)"),
     *             @OA\Property(property="historique_livraison_id", type="integer", example=1, description="ID de l'historique de livraison (optionnel, déprécié)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Position mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Position mise à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="location_id", type="integer", example=123),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z"),
     *                 @OA\Property(property="server_timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z"),
     *                 @OA\Property(property="context", type="object",
     *                     @OA\Property(property="type", type="string", example="ramassage"),
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="RAMS-IY4LXYU0"),
     *                     @OA\Property(property="adresse", type="string", example="Cocody, palmeraie"),
     *                     @OA\Property(property="client", type="string", example="Joel Dje-Bi"),
     *                     @OA\Property(property="telephone", type="string", example="0758754662")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
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
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0|max:1000',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'timestamp' => 'nullable|date',
            'status' => 'nullable|in:en_cours,en_pause,termine',
            'context_type' => 'nullable|in:ramassage,livraison,libre',
            'context_id' => 'nullable|integer|min:1',
            'ramassage_id' => 'nullable|exists:ramassages,id',
            'historique_livraison_id' => 'nullable|exists:historique_livraisons,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $livreurId = Auth::id();
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            // Rate limiting - max 1 position par seconde
            $lastLocation = LivreurLocation::where('livreur_id', $livreurId)
                ->where('timestamp', '>=', now()->subSeconds(1))
                ->first();

            if ($lastLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trop de requêtes. Attendez 1 seconde.'
                ], 429);
            }

            // Gestion du contexte de mission
            $ramassageId = null;
            $historiqueLivraisonId = null;
            $context = null;

            if ($request->context_type && $request->context_id) {
                if ($request->context_type === 'ramassage') {
                    $ramassageId = $request->context_id;
                } elseif ($request->context_type === 'livraison') {
                    $historiqueLivraisonId = $request->context_id;
                }
            } else {
                // Fallback pour compatibilité avec l'ancien système
                $ramassageId = $request->ramassage_id;
                $historiqueLivraisonId = $request->historique_livraison_id;
            }

            $location = LivreurLocation::create([
                'livreur_id' => $livreurId,
                'entreprise_id' => $entrepriseId,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'altitude' => $request->altitude,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'timestamp' => $request->timestamp ? Carbon::parse($request->timestamp) : now(),
                'status' => $request->status ?? 'en_cours',
                'ramassage_id' => $ramassageId,
                'historique_livraison_id' => $historiqueLivraisonId
            ]);

            // Récupérer les informations de contexte si disponibles
            if ($ramassageId) {
                $ramassage = \App\Models\Ramassage::find($ramassageId);
                if ($ramassage) {
                    $context = [
                        'type' => 'ramassage',
                        'id' => $ramassage->id,
                        'code' => $ramassage->code_ramassage,
                        'adresse' => $ramassage->adresse_ramassage,
                        'client' => $ramassage->contact_ramassage,
                        'telephone' => $ramassage->contact_ramassage
                    ];
                }
            } elseif ($historiqueLivraisonId) {
                $historique = \App\Models\Historique_livraison::with('packageColis.colis')->find($historiqueLivraisonId);
                if ($historique && $historique->packageColis && $historique->packageColis->colis) {
                    $colis = $historique->packageColis->colis;
                    $context = [
                        'type' => 'livraison',
                        'id' => $historique->id,
                        'code' => $colis->code ?? 'CLIS-' . str_pad($colis->id, 6, '0', STR_PAD_LEFT),
                        'adresse' => $colis->adresse_client,
                        'client' => $colis->nom_client,
                        'telephone' => $colis->telephone_client
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Position mise à jour avec succès',
                'data' => [
                    'location_id' => $location->id,
                    'timestamp' => $location->timestamp->toISOString(),
                    'server_timestamp' => now()->toISOString(),
                    'context' => $context
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/location/history",
     *     summary="Récupérer l'historique des positions du livreur",
     *     description="Récupère l'historique des positions GPS du livreur avec filtres optionnels",
     *     tags={"Géolocalisation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="livreur_id",
     *         in="query",
     *         description="ID du livreur (optionnel, par défaut: livreur authentifié)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Date de début (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-23")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Date de fin (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-24")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre maximum de résultats (1-1000)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, example=100)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"en_cours", "en_pause", "termine"}, example="en_cours")
     *     ),
     *     @OA\Parameter(
     *         name="context_type",
     *         in="query",
     *         description="Filtrer par type de mission",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ramassage", "livraison", "libre"}, example="ramassage")
     *     ),
     *     @OA\Parameter(
     *         name="context_id",
     *         in="query",
     *         description="Filtrer par ID de mission spécifique",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="mission_type",
     *         in="query",
     *         description="Alias pour context_type - Filtrer par type de mission",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ramassage", "livraison", "libre"}, example="livraison")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique récupéré avec succès (données optimisées)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=123),
     *                     @OA\Property(property="livreur_id", type="integer", example=1),
     *                     @OA\Property(property="latitude", type="string", example="5.31666700"),
     *                     @OA\Property(property="longitude", type="string", example="-4.03333300"),
     *                     @OA\Property(property="accuracy", type="string", example="10.50"),
     *                     @OA\Property(property="speed", type="string", example="15.50"),
     *                     @OA\Property(property="heading", type="string", example="180.00"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="en_cours")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer", example=50),
     *             @OA\Property(property="filters", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="start_date", type="string", example="2025-10-23"),
     *                 @OA\Property(property="end_date", type="string", example="2025-10-23"),
     *                 @OA\Property(property="status", type="string", example="en_cours")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Paramètres invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Paramètres invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     )
     * )
     * Récupérer l'historique des positions
     * GET /api/livreur/location/history
     */
    public function getLocationHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'livreur_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:1000',
            'status' => 'nullable|in:en_cours,en_pause,termine',
            'context_type' => 'nullable|in:ramassage,livraison,libre',
            'context_id' => 'nullable|integer|min:1',
            'mission_type' => 'nullable|in:ramassage,livraison,libre'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètres invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            $query = LivreurLocation::select([
                'id',
                'livreur_id',
                'latitude',
                'longitude',
                'accuracy',
                'speed',
                'heading',
                'timestamp',
                'status'
            ])->where('entreprise_id', $entrepriseId);

            // Si pas de livreur_id spécifié, utiliser l'utilisateur connecté
            $livreurId = $request->livreur_id ?? Auth::id();
            $query->where('livreur_id', $livreurId);

            // Filtres de date
            if ($request->start_date) {
                $query->where('timestamp', '>=', Carbon::parse($request->start_date));
            }
            if ($request->end_date) {
                $query->where('timestamp', '<=', Carbon::parse($request->end_date));
            }

            // Filtre de statut
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Filtres par type de mission
            if ($request->context_type) {
                if ($request->context_type === 'ramassage') {
                    $query->whereNotNull('ramassage_id');
                } elseif ($request->context_type === 'livraison') {
                    $query->whereNotNull('historique_livraison_id');
                } elseif ($request->context_type === 'libre') {
                    $query->whereNull('ramassage_id')->whereNull('historique_livraison_id');
                }
            }

            // Filtre par ID de mission spécifique
            if ($request->context_id) {
                if ($request->context_type === 'ramassage') {
                    $query->where('ramassage_id', $request->context_id);
                } elseif ($request->context_type === 'livraison') {
                    $query->where('historique_livraison_id', $request->context_id);
                }
            }

            // Filtre par type de mission (alias pour context_type)
            if ($request->mission_type) {
                if ($request->mission_type === 'ramassage') {
                    $query->whereNotNull('ramassage_id');
                } elseif ($request->mission_type === 'livraison') {
                    $query->whereNotNull('historique_livraison_id');
                } elseif ($request->mission_type === 'libre') {
                    $query->whereNull('ramassage_id')->whereNull('historique_livraison_id');
                }
            }

            // Limite et tri
            $limit = $request->limit ?? 100;
            $locations = $query->orderBy('timestamp', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $locations,
                'count' => $locations->count(),
                'filters' => [
                    'livreur_id' => $livreurId,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => $request->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/location/status",
     *     summary="Mettre à jour le statut de localisation du livreur",
     *     description="Met à jour le statut de localisation du livreur (actif, inactif, en pause)",
     *     tags={"Géolocalisation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "paused"}, example="active", description="Statut de localisation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statut mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="last_updated", type="string", format="date-time", example="2025-10-23T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Statut invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Statut invalide"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     )
     * )
     * Mettre à jour le statut de localisation
     * POST /api/livreur/location/status
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,paused'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Statut invalide',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $livreurId = Auth::id();
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;
            $status = $request->status;

            LivreurLocationStatus::updateStatus($livreurId, $status, null, $entrepriseId);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => [
                    'livreur_id' => $livreurId,
                    'status' => $status,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/location/status",
     *     summary="Récupérer le statut de localisation du livreur",
     *     description="Récupère le statut actuel de localisation du livreur",
     *     tags={"Géolocalisation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statut récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Statut récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="socket_id", type="string", example="socket_123"),
     *                 @OA\Property(property="last_updated", type="string", format="date-time", example="2025-10-23T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Statut non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Statut non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     )
     * )
     * Récupérer le statut actuel
     * GET /api/livreur/location/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        try {
            $livreurId = Auth::id();
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            $status = LivreurLocationStatus::where('livreur_id', $livreurId)
                ->where('entreprise_id', $entrepriseId)
                ->first();

            if (!$status) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'livreur_id' => $livreurId,
                        'status' => 'inactive',
                        'last_updated' => null,
                        'socket_id' => null
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'livreur_id' => $status->livreur_id,
                    'status' => $status->status,
                    'last_updated' => $status->last_updated->toISOString(),
                    'socket_id' => $status->socket_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/location/livreurs",
     *     summary="Récupérer les positions de tous les livreurs (Admin)",
     *     description="Récupère les positions récentes de tous les livreurs pour le monitoring admin",
     *     tags={"Géolocalisation", "Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut des livreurs",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "paused"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre maximum de livreurs à retourner",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Positions des livreurs récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Positions des livreurs récupérées avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="livreur_id", type="integer", example=1),
     *                     @OA\Property(property="livreur_name", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="latitude", type="number", format="float", example=5.316667),
     *                     @OA\Property(property="longitude", type="number", format="float", example=-4.033333),
     *                     @OA\Property(property="accuracy", type="number", format="float", example=10.5),
     *                     @OA\Property(property="speed", type="number", format="float", example=15.5),
     *                     @OA\Property(property="heading", type="number", format="float", example=180.0),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="socket_id", type="string", example="socket_123"),
     *                     @OA\Property(property="last_updated", type="string", format="date-time", example="2025-10-23T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="total_livreurs", type="integer", example=25),
     *             @OA\Property(property="active_livreurs", type="integer", example=20),
     *             @OA\Property(property="inactive_livreurs", type="integer", example=3),
     *             @OA\Property(property="paused_livreurs", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé - Admin requis",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès refusé - Admin requis")
     *         )
     *     )
     * )
     * Récupérer les positions récentes de tous les livreurs (pour admins)
     * GET /api/admin/location/livreurs
     */
    public function getAllLivreursLocations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            $query = LivreurLocation::with(['livreur', 'ramassage', 'historiqueLivraison', 'entreprise'])
                ->where('entreprise_id', $entrepriseId)
                ->recent(30) // 30 dernières minutes
                ->orderBy('timestamp', 'desc');

            // Filtre par statut
            if ($request->status) {
                $query->where('status', $request->status);
            }

            $locations = $query->get();

            // Grouper par livreur pour avoir la dernière position
            $groupedLocations = $locations->groupBy('livreur_id')->map(function ($livreurLocations) {
                $latest = $livreurLocations->first();
                return [
                    'livreur_id' => $latest->livreur_id,
                    'entreprise_id' => $latest->entreprise_id,
                    'livreur_name' => $latest->livreur->name ?? 'Inconnu',
                    'latitude' => $latest->latitude,
                    'longitude' => $latest->longitude,
                    'accuracy' => $latest->accuracy,
                    'speed' => $latest->speed,
                    'heading' => $latest->heading,
                    'status' => $latest->status,
                    'timestamp' => $latest->timestamp->toISOString(),
                    'ramassage_id' => $latest->ramassage_id,
                    'historique_livraison_id' => $latest->historique_livraison_id
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $groupedLocations->values(),
                'count' => $groupedLocations->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/location/current-mission",
     *     summary="Récupérer la mission actuelle du livreur",
     *     description="Récupère la mission actuelle du livreur (ramassage ou livraison) avec les détails et la dernière position",
     *     tags={"Géolocalisation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mission actuelle récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="mission", type="object",
     *                     @OA\Property(property="type", type="string", example="ramassage"),
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="RAMS-IY4LXYU0"),
     *                     @OA\Property(property="adresse", type="string", example="Cocody, palmeraie"),
     *                     @OA\Property(property="client", type="string", example="Joel Dje-Bi"),
     *                     @OA\Property(property="telephone", type="string", example="0758754662"),
     *                     @OA\Property(property="status", type="string", example="en_cours"),
     *                     @OA\Property(property="date_debut", type="string", format="date-time", example="2025-10-23T10:00:00Z")
     *                 ),
     *                 @OA\Property(property="last_position", type="object",
     *                     @OA\Property(property="latitude", type="string", example="5.31666700"),
     *                     @OA\Property(property="longitude", type="string", example="-4.03333300"),
     *                     @OA\Property(property="accuracy", type="string", example="10.50"),
     *                     @OA\Property(property="speed", type="string", example="15.50"),
     *                     @OA\Property(property="heading", type="string", example="180.00"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune mission en cours",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucune mission en cours"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="livreur_id", type="integer", example=1),
     *                 @OA\Property(property="mission", type="null"),
     *                 @OA\Property(property="last_position", type="object",
     *                     @OA\Property(property="latitude", type="string", example="5.31666700"),
     *                     @OA\Property(property="longitude", type="string", example="-4.03333300"),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Récupérer la mission actuelle du livreur
     * GET /api/livreur/location/current-mission
     */
    public function getCurrentMission(): JsonResponse
    {
        try {
            $livreurId = Auth::id();
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            // Récupérer la dernière position
            $lastLocation = LivreurLocation::where('livreur_id', $livreurId)
                ->where('entreprise_id', $entrepriseId)
                ->orderBy('timestamp', 'desc')
                ->first();

            $mission = null;

            // Vérifier s'il y a une mission en cours
            if ($lastLocation) {
                if ($lastLocation->ramassage_id) {
                    // Mission de ramassage
                    $ramassage = \App\Models\Ramassage::find($lastLocation->ramassage_id);
                    if ($ramassage && in_array($ramassage->statut, ['planifie', 'en_cours'])) {
                        $mission = [
                            'type' => 'ramassage',
                            'id' => $ramassage->id,
                            'code' => $ramassage->code_ramassage,
                            'adresse' => $ramassage->adresse_ramassage,
                            'client' => $ramassage->contact_ramassage,
                            'telephone' => $ramassage->contact_ramassage,
                            'status' => $ramassage->statut,
                            'date_debut' => $ramassage->date_debut_ramassage ? $ramassage->date_debut_ramassage->toISOString() : null
                        ];
                    }
                } elseif ($lastLocation->historique_livraison_id) {
                    // Mission de livraison
                    $historique = \App\Models\Historique_livraison::with('packageColis.colis')->find($lastLocation->historique_livraison_id);
                    if ($historique && $historique->packageColis && $historique->packageColis->colis) {
                        $colis = $historique->packageColis->colis;
                        if (in_array($colis->status, [0, 1])) { // en_attente ou en_cours
                            $mission = [
                                'type' => 'livraison',
                                'id' => $historique->id,
                                'code' => $colis->code ?? 'CLIS-' . str_pad($colis->id, 6, '0', STR_PAD_LEFT),
                                'adresse' => $colis->adresse_client,
                                'client' => $colis->nom_client,
                                'telephone' => $colis->telephone_client,
                                'status' => $colis->status == 0 ? 'en_attente' : 'en_cours',
                                'date_debut' => $colis->created_at ? $colis->created_at->toISOString() : null
                            ];
                        }
                    }
                }
            }

            $responseData = [
                'livreur_id' => $livreurId,
                'mission' => $mission,
                'last_position' => $lastLocation ? [
                    'latitude' => $lastLocation->latitude,
                    'longitude' => $lastLocation->longitude,
                    'accuracy' => $lastLocation->accuracy,
                    'speed' => $lastLocation->speed,
                    'heading' => $lastLocation->heading,
                    'timestamp' => $lastLocation->timestamp->toISOString()
                ] : null
            ];

            if ($mission) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune mission en cours',
                    'data' => $responseData
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/location/mission-history/{mission_type}/{mission_id}",
     *     summary="Récupérer l'historique d'une mission spécifique",
     *     description="Récupère l'historique des positions pour une mission spécifique (ramassage ou livraison)",
     *     tags={"Géolocalisation"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="mission_type",
     *         in="path",
     *         description="Type de mission",
     *         required=true,
     *         @OA\Schema(type="string", enum={"ramassage", "livraison"}, example="ramassage")
     *     ),
     *     @OA\Parameter(
     *         name="mission_id",
     *         in="path",
     *         description="ID de la mission",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre maximum de positions à retourner",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, example=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique de mission récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mission", type="object",
     *                     @OA\Property(property="type", type="string", example="ramassage"),
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="RAMS-IY4LXYU0"),
     *                     @OA\Property(property="adresse", type="string", example="Cocody, palmeraie"),
     *                     @OA\Property(property="client", type="string", example="Joel Dje-Bi"),
     *                     @OA\Property(property="telephone", type="string", example="0758754662")
     *                 ),
     *                 @OA\Property(property="positions", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=123),
     *                         @OA\Property(property="latitude", type="string", example="5.31666700"),
     *                         @OA\Property(property="longitude", type="string", example="-4.03333300"),
     *                         @OA\Property(property="accuracy", type="string", example="10.50"),
     *                         @OA\Property(property="speed", type="string", example="15.50"),
     *                         @OA\Property(property="heading", type="string", example="180.00"),
     *                         @OA\Property(property="timestamp", type="string", format="date-time", example="2025-10-23T12:00:00Z"),
     *                         @OA\Property(property="status", type="string", example="en_cours")
     *                     )
     *                 ),
     *                 @OA\Property(property="count", type="integer", example=25),
     *                 @OA\Property(property="distance_total", type="number", format="float", example=5.2),
     *                 @OA\Property(property="duree_total", type="string", example="00:45:30")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mission non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Mission non trouvée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Récupérer l'historique d'une mission spécifique
     * GET /api/livreur/location/mission-history/{mission_type}/{mission_id}
     */
    public function getMissionHistory($missionType, $missionId): JsonResponse
    {
        try {
            $livreurId = Auth::id();
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            // Validation du type de mission
            if (!in_array($missionType, ['ramassage', 'livraison'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type de mission invalide. Utilisez "ramassage" ou "livraison".'
                ], 422);
            }

            $mission = null;
            $query = LivreurLocation::select([
                'id', 'livreur_id', 'latitude', 'longitude',
                'accuracy', 'speed', 'heading', 'timestamp', 'status'
            ])->where('livreur_id', $livreurId)
              ->where('entreprise_id', $entrepriseId);

            // Construire la requête selon le type de mission
            if ($missionType === 'ramassage') {
                $query->where('ramassage_id', $missionId);
                $ramassage = \App\Models\Ramassage::find($missionId);
                if (!$ramassage || $ramassage->livreur_id != $livreurId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ramassage non trouvé'
                    ], 404);
                }
                $mission = [
                    'type' => 'ramassage',
                    'id' => $ramassage->id,
                    'code' => $ramassage->code_ramassage,
                    'adresse' => $ramassage->adresse_ramassage,
                    'client' => $ramassage->contact_ramassage,
                    'telephone' => $ramassage->contact_ramassage
                ];
            } else {
                $query->where('historique_livraison_id', $missionId);
                $historique = \App\Models\Historique_livraison::with('packageColis.colis')->find($missionId);
                if (!$historique || !$historique->packageColis || !$historique->packageColis->colis) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Livraison non trouvée'
                    ], 404);
                }
                $colis = $historique->packageColis->colis;
                $mission = [
                    'type' => 'livraison',
                    'id' => $historique->id,
                    'code' => $colis->code ?? 'CLIS-' . str_pad($colis->id, 6, '0', STR_PAD_LEFT),
                    'adresse' => $colis->adresse_client,
                    'client' => $colis->nom_client,
                    'telephone' => $colis->telephone_client
                ];
            }

            // Récupérer les positions
            $positions = $query->orderBy('timestamp', 'asc')->get();

            // Calculer la distance totale et la durée
            $distanceTotal = 0;
            $dureeTotal = 0;

            if ($positions->count() > 1) {
                for ($i = 1; $i < $positions->count(); $i++) {
                    $prev = $positions[$i - 1];
                    $curr = $positions[$i];

                    // Calculer la distance entre deux points (formule de Haversine)
                    $lat1 = deg2rad($prev->latitude);
                    $lon1 = deg2rad($prev->longitude);
                    $lat2 = deg2rad($curr->latitude);
                    $lon2 = deg2rad($curr->longitude);

                    $dlat = $lat2 - $lat1;
                    $dlon = $lon2 - $lon1;

                    $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
                    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                    $distance = 6371 * $c; // Rayon de la Terre en km

                    $distanceTotal += $distance;
                }

                // Calculer la durée totale
                $startTime = $positions->first()->timestamp;
                $endTime = $positions->last()->timestamp;
                $dureeTotal = $startTime->diffInSeconds($endTime);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'mission' => $mission,
                    'positions' => $positions,
                    'count' => $positions->count(),
                    'distance_total' => round($distanceTotal, 2),
                    'duree_total' => gmdate('H:i:s', $dureeTotal)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}
