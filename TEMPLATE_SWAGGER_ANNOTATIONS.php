<?php

/**
 * 🚀 TEMPLATE SWAGGER ANNOTATIONS
 *
 * Ce fichier contient des templates d'annotations Swagger pour les nouveaux endpoints.
 * Copiez et adaptez ces templates pour documenter vos nouvelles APIs.
 */

// ========================================
// 1. ENDPOINT GET (Récupération de données)
// ========================================

/**
 * @OA\Get(
 *     path="/api/livreur/endpoint-get",
 *     summary="Description courte de l'endpoint",
 *     description="Description détaillée de ce que fait cet endpoint",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="param1",
 *         in="query",
 *         description="Description du paramètre",
 *         required=false,
 *         @OA\Schema(type="string", example="valeur_exemple")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Opération réussie"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="nom", type="string", example="Exemple")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Token invalide")
 *         )
 *     )
 * )
 */
public function endpointGet(Request $request)
{
    // Implémentation
}

// ========================================
// 2. ENDPOINT POST (Création de données)
// ========================================

/**
 * @OA\Post(
 *     path="/api/livreur/endpoint-post",
 *     summary="Créer une nouvelle ressource",
 *     description="Description de ce que crée cet endpoint",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"champ_requis"},
 *             @OA\Property(property="champ_requis", type="string", example="valeur", description="Description du champ"),
 *             @OA\Property(property="champ_optionnel", type="string", example="valeur", description="Description du champ optionnel")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Créé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Ressource créée avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Données de validation invalides"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
public function endpointPost(Request $request)
{
    // Implémentation
}

// ========================================
// 3. ENDPOINT PUT (Mise à jour de données)
// ========================================

/**
 * @OA\Put(
 *     path="/api/livreur/endpoint-put/{id}",
 *     summary="Mettre à jour une ressource",
 *     description="Description de ce que met à jour cet endpoint",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID de la ressource à mettre à jour",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="nom", type="string", example="Nouveau nom", description="Nouveau nom"),
 *             @OA\Property(property="description", type="string", example="Nouvelle description", description="Nouvelle description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Mis à jour avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Ressource mise à jour avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ressource non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
 *         )
 *     )
 * )
 */
public function endpointPut(Request $request, $id)
{
    // Implémentation
}

// ========================================
// 4. ENDPOINT DELETE (Suppression de données)
// ========================================

/**
 * @OA\Delete(
 *     path="/api/livreur/endpoint-delete/{id}",
 *     summary="Supprimer une ressource",
 *     description="Description de ce que supprime cet endpoint",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID de la ressource à supprimer",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Supprimé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Ressource supprimée avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ressource non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Ressource non trouvée")
 *         )
 *     )
 * )
 */
public function endpointDelete($id)
{
    // Implémentation
}

// ========================================
// 5. ENDPOINT AVEC PAGINATION
// ========================================

/**
 * @OA\Get(
 *     path="/api/livreur/endpoint-paginated",
 *     summary="Liste paginée de ressources",
 *     description="Récupère une liste paginée de ressources",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Numéro de page",
 *         required=false,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Nombre d'éléments par page",
 *         required=false,
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste récupérée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Liste récupérée avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="per_page", type="integer", example=10),
 *                 @OA\Property(property="total", type="integer", example=100),
 *                 @OA\Property(property="last_page", type="integer", example=10),
 *                 @OA\Property(
 *                     property="data",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="nom", type="string", example="Exemple")
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */
public function endpointPaginated(Request $request)
{
    // Implémentation
}

// ========================================
// 6. ENDPOINT AVEC UPLOAD DE FICHIER
// ========================================

/**
 * @OA\Post(
 *     path="/api/livreur/endpoint-upload",
 *     summary="Upload de fichier",
 *     description="Upload d'un fichier (image, document, etc.)",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary",
 *                     description="Fichier à uploader"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     description="Description du fichier"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Fichier uploadé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Fichier uploadé avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="filename", type="string", example="image.jpg"),
 *                 @OA\Property(property="url", type="string", example="http://127.0.0.1:8000/storage/uploads/image.jpg")
 *             )
 *         )
 *     )
 * )
 */
public function endpointUpload(Request $request)
{
    // Implémentation
}

// ========================================
// 7. ENDPOINT AVEC RECHERCHE/FILTRES
// ========================================

/**
 * @OA\Get(
 *     path="/api/livreur/endpoint-search",
 *     summary="Recherche avec filtres",
 *     description="Recherche de ressources avec différents filtres",
 *     tags={"Tag Approprié"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Terme de recherche",
 *         required=false,
 *         @OA\Schema(type="string", example="livreur")
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filtrer par statut",
 *         required=false,
 *         @OA\Schema(type="string", enum={"actif", "inactif"}, example="actif")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         description="Date de début (YYYY-MM-DD)",
 *         required=false,
 *         @OA\Schema(type="string", format="date", example="2025-01-01")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         description="Date de fin (YYYY-MM-DD)",
 *         required=false,
 *         @OA\Schema(type="string", format="date", example="2025-12-31")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recherche effectuée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Recherche effectuée avec succès"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="nom", type="string", example="Résultat de recherche")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
public function endpointSearch(Request $request)
{
    // Implémentation
}

// ========================================
// 8. NOUVEAU TAG
// ========================================

/**
 * @OA\Tag(
 *     name="Nouveau Tag",
 *     description="Description du nouveau tag pour organiser les endpoints"
 * )
 */

// ========================================
// 9. NOUVEAU SCHÉMA DE DONNÉES
// ========================================

/**
 * @OA\Schema(
 *     schema="NouveauModele",
 *     type="object",
 *     title="Nouveau Modèle",
 *     description="Description du nouveau modèle de données",
 *     @OA\Property(property="id", type="integer", example=1, description="ID unique"),
 *     @OA\Property(property="nom", type="string", example="Exemple", description="Nom du modèle"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de mise à jour")
 * )
 */

// ========================================
// 10. UTILISATION D'UN SCHÉMA EXISTANT
// ========================================

/**
 * @OA\Get(
 *     path="/api/livreur/endpoint-with-schema",
 *     summary="Endpoint utilisant un schéma",
 *     tags={"Tag Approprié"},
 *     @OA\Response(
 *         response=200,
 *         description="Succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/NouveauModele"
 *             )
 *         )
 *     )
 * )
 */
public function endpointWithSchema()
{
    // Implémentation
}

// ========================================
// INSTRUCTIONS D'UTILISATION
// ========================================

/*
1. Copiez le template approprié pour votre endpoint
2. Adaptez les chemins, descriptions et paramètres
3. Ajoutez les annotations au-dessus de votre méthode
4. Mettez à jour la documentation : php artisan l5-swagger:generate
5. Testez dans Swagger UI : http://127.0.0.1:8000/api/documentation

TAGS DISPONIBLES :
- Authentification Livreur
- Profil Livreur
- Colis
- Géolocalisation
- Statistiques

CODES DE RÉPONSE COURANTS :
- 200 : Succès
- 201 : Créé
- 400 : Erreur de requête
- 401 : Non autorisé
- 403 : Interdit
- 404 : Non trouvé
- 422 : Erreur de validation
- 500 : Erreur serveur
*/