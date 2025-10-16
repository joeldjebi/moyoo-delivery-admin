<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @OA\Tag(
 *     name="Authentification Livreur",
 *     description="Endpoints d'authentification pour les livreurs"
 * )
 */
class LivreurAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/livreur/login",
     *     summary="Connexion du livreur",
     *     description="Authentification d'un livreur avec son mobile et mot de passe",
     *     tags={"Authentification Livreur"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile","password"},
     *             @OA\Property(property="mobile", type="string", example="1234567890", description="Numéro de mobile du livreur"),
     *             @OA\Property(property="password", type="string", example="password123", description="Mot de passe du livreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="refresh_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=172800),
     *                 @OA\Property(property="refresh_expires_in", type="integer", example=259200),
     *                 @OA\Property(
     *                     property="livreur",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="nom_complet", type="string", example="Test Livreur"),
     *                     @OA\Property(property="mobile", type="string", example="1234567890"),
     *                     @OA\Property(property="email", type="string", example=null),
     *                     @OA\Property(property="status", type="string", example="actif"),
     *                     @OA\Property(property="photo", type="string", example=null),
     *                     @OA\Property(
     *                         property="engin",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example=null),
     *                         @OA\Property(property="type", type="string", example="Moto")
     *                     ),
     *                     @OA\Property(property="zone_activite", type="object", example=null)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Identifiants incorrects")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Compte inactif",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Votre compte est inactif. Contactez votre administrateur.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Données de validation invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier les identifiants
            $livreur = Livreur::where('mobile', $request->mobile)->first();

            if (!$livreur || !Hash::check($request->password, $livreur->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ], 401);
            }

            // Vérifier le statut du livreur
            if ($livreur->status !== 'actif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte est inactif. Contactez votre administrateur.'
                ], 403);
            }

            // Générer le token JWT et le refresh token
            $token = JWTAuth::fromUser($livreur);
            $refreshToken = JWTAuth::customClaims(['type' => 'refresh'])->fromUser($livreur);

            if (!$token || !$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de créer le token'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60, // en secondes
                    'refresh_expires_in' => config('jwt.refresh_ttl') * 60, // en secondes
                    'livreur' => [
                        'id' => $livreur->id,
                        'nom_complet' => $livreur->nom_complet,
                        'mobile' => $livreur->mobile,
                        'email' => $livreur->email,
                        'status' => $livreur->status,
                        'photo' => $livreur->photo ? asset('storage/' . $livreur->photo) : null,
                        'engin' => $livreur->engin ? [
                            'id' => $livreur->engin->id,
                            'nom' => $livreur->engin->nom,
                            'type' => $livreur->engin->typeEngin ? $livreur->engin->typeEngin->libelle : null
                        ] : null,
                        'zone_activite' => $livreur->zoneActivite ? [
                            'id' => $livreur->zoneActivite->id,
                            'libelle' => $livreur->zoneActivite->libelle
                        ] : null
                    ]
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du token'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/logout",
     *     summary="Déconnexion du livreur",
     *     description="Déconnexion d'un livreur et invalidation du token JWT",
     *     tags={"Authentification Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/refresh",
     *     summary="Rafraîchir le token JWT",
     *     description="Rafraîchit le token JWT du livreur connecté",
     *     tags={"Authentification Livreur"},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token rafraîchi avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="refresh_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=172800),
     *                 @OA\Property(property="refresh_expires_in", type="integer", example=259200)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Impossible de rafraîchir le token")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        try {
            $oldToken = JWTAuth::getToken();
            $token = JWTAuth::refresh($oldToken);

            // Générer un nouveau refresh token
            $livreur = JWTAuth::toUser($token);
            $refreshToken = JWTAuth::customClaims(['type' => 'refresh'])->fromUser($livreur);

            return response()->json([
                'success' => true,
                'message' => 'Token rafraîchi avec succès',
                'data' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60, // en secondes
                    'refresh_expires_in' => config('jwt.refresh_ttl') * 60 // en secondes
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de rafraîchir le token'
            ], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/profile",
     *     summary="Obtenir le profil du livreur",
     *     description="Récupère les informations complètes du livreur connecté",
     *     tags={"Profil Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profil récupéré avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="nom_complet", type="string", example="Test Livreur"),
     *                 @OA\Property(property="first_name", type="string", example="Test"),
     *                 @OA\Property(property="last_name", type="string", example="Livreur"),
     *                 @OA\Property(property="mobile", type="string", example="1234567890"),
     *                 @OA\Property(property="email", type="string", example=null),
     *                 @OA\Property(property="adresse", type="string", example=null),
     *                 @OA\Property(property="permis", type="string", example=null),
     *                 @OA\Property(property="status", type="string", example="actif"),
     *                 @OA\Property(property="photo", type="string", example=null),
     *                 @OA\Property(
     *                     property="engin",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example=null),
     *                     @OA\Property(property="type", type="string", example="Moto")
     *                 ),
     *                 @OA\Property(property="zone_activite", type="object", example=null),
     *                 @OA\Property(property="communes", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Livreur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Livreur non trouvé")
     *         )
     *     )
     * )
     */
    public function profile()
    {
        try {
            $livreur = Auth::guard('livreur')->user();

            if (!$livreur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livreur non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil récupéré avec succès',
                'data' => [
                    'id' => $livreur->id,
                    'nom_complet' => $livreur->nom_complet,
                    'first_name' => $livreur->first_name,
                    'last_name' => $livreur->last_name,
                    'mobile' => $livreur->mobile,
                    'email' => $livreur->email,
                    'adresse' => $livreur->adresse,
                    'permis' => $livreur->permis,
                    'status' => $livreur->status,
                    'photo' => $livreur->photo ? asset('storage/' . $livreur->photo) : null,
                    'engin' => $livreur->engin ? [
                        'id' => $livreur->engin->id,
                        'nom' => $livreur->engin->libelle,
                        'type' => $livreur->engin->typeEngin ? $livreur->engin->typeEngin->libelle : null
                    ] : null,
                    'zone_activite' => $livreur->zoneActivite ? [
                        'id' => $livreur->zoneActivite->id,
                        'libelle' => $livreur->zoneActivite->libelle
                    ] : null,
                    'communes' => $livreur->communes->map(function ($commune) {
                        return [
                            'id' => $commune->id,
                            'libelle' => $commune->libelle
                        ];
                    }),
                    'created_at' => $livreur->created_at,
                    'updated_at' => $livreur->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/profile",
     *     summary="Mettre à jour le profil du livreur",
     *     description="Met à jour les informations du profil du livreur connecté",
     *     tags={"Profil Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="first_name", type="string", example="Jean", description="Prénom du livreur"),
     *                 @OA\Property(property="last_name", type="string", example="Dupont", description="Nom du livreur"),
     *                 @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com", description="Email du livreur"),
     *                 @OA\Property(property="adresse", type="string", example="123 Rue de la Paix", description="Adresse du livreur"),
     *                 @OA\Property(property="photo", type="string", format="binary", description="Photo de profil (fichier image)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profil mis à jour avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="nom_complet", type="string", example="Jean Dupont"),
     *                 @OA\Property(property="first_name", type="string", example="Jean"),
     *                 @OA\Property(property="last_name", type="string", example="Dupont"),
     *                 @OA\Property(property="mobile", type="string", example="1234567890"),
     *                 @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *                 @OA\Property(property="adresse", type="string", example="123 Rue de la Paix"),
     *                 @OA\Property(property="photo", type="string", example="http://192.168.1.9:8000/storage/livreurs/photo.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Données de validation invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        // Gérer les requêtes multipart/form-data
        $data = $request->all();

        // Si les données sont vides (problème avec multipart/form-data),
        // essayer de récupérer les données via input()
        if (empty($data)) {
            $data = [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'adresse' => $request->input('adresse')
            ];
        }

        $validator = Validator::make($data, [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'adresse' => 'sometimes|string|max:500',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $livreur = Auth::guard('livreur')->user();

            if (!$livreur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livreur non trouvé'
                ], 404);
            }

            // Mettre à jour les champs
            $livreur->fill($data);

            // Gérer l'upload de la photo
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('livreurs', 'public');
                $livreur->photo = $photoPath;
            }

            $livreur->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'id' => $livreur->id,
                    'nom_complet' => $livreur->nom_complet,
                    'first_name' => $livreur->first_name,
                    'last_name' => $livreur->last_name,
                    'mobile' => $livreur->mobile,
                    'email' => $livreur->email,
                    'adresse' => $livreur->adresse,
                    'photo' => $livreur->photo ? asset('storage/' . $livreur->photo) : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/livreur/change-password",
     *     summary="Changer le mot de passe",
     *     description="Permet au livreur de changer son mot de passe",
     *     tags={"Profil Livreur"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="ancienMotDePasse123", description="Mot de passe actuel"),
     *             @OA\Property(property="new_password", type="string", example="nouveauMotDePasse123", description="Nouveau mot de passe (minimum 8 caractères)"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="nouveauMotDePasse123", description="Confirmation du nouveau mot de passe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe modifié avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mot de passe actuel incorrect",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Mot de passe actuel incorrect")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données de validation invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Données de validation invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $livreur = Auth::guard('livreur')->user();

            if (!Hash::check($request->current_password, $livreur->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mot de passe actuel incorrect'
                ], 400);
            }

            $livreur->password = Hash::make($request->new_password);
            $livreur->save();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe modifié avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe: ' . $e->getMessage()
            ], 500);
        }
    }
}
