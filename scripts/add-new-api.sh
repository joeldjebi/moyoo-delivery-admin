#!/bin/bash

# Script pour ajouter une nouvelle API avec documentation Swagger
# Usage: ./scripts/add-new-api.sh "Nom de l'API" "Description"

API_NAME="$1"
API_DESCRIPTION="$2"

if [ -z "$API_NAME" ] || [ -z "$API_DESCRIPTION" ]; then
    echo "❌ Usage: $0 \"Nom de l'API\" \"Description\""
    echo "   Exemple: $0 \"Colis Assignés\" \"Récupérer les colis assignés au livreur\""
    exit 1
fi

echo "🚀 Ajout d'une nouvelle API: $API_NAME"
echo "📝 Description: $API_DESCRIPTION"

# Aller dans le répertoire du projet
cd "$(dirname "$0")/.."

# Créer le nom de fichier (en minuscules, avec des underscores)
FILE_NAME=$(echo "$API_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/ /_/g' | sed 's/é/e/g' | sed 's/è/e/g' | sed 's/à/a/g')

echo "📁 Nom du fichier: ${FILE_NAME}_controller.php"

# Créer le contrôleur avec template Swagger
cat > "app/Http/Controllers/Api/${FILE_NAME}_controller.php" << 'EOF'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="$API_NAME",
 *     description="$API_DESCRIPTION"
 * )
 */
class ${FILE_NAME^}Controller extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/livreur/$FILE_NAME",
     *     summary="Liste des $API_NAME",
     *     description="$API_DESCRIPTION",
     *     tags={"$API_NAME"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Liste récupérée avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Exemple")
     *                 )
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
    public function index(Request \$request)
    {
        // TODO: Implémenter la logique
        return response()->json([
            'success' => true,
            'message' => 'Liste récupérée avec succès',
            'data' => []
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/livreur/$FILE_NAME/{id}",
     *     summary="Détails d'un élément",
     *     description="Récupère les détails d'un élément spécifique",
     *     tags={"$API_NAME"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'élément",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Détails récupérés avec succès"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Exemple")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Élément non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Élément non trouvé")
     *         )
     *     )
     * )
     */
    public function show(\$id)
    {
        // TODO: Implémenter la logique
        return response()->json([
            'success' => true,
            'message' => 'Détails récupérés avec succès',
            'data' => ['id' => \$id, 'nom' => 'Exemple']
        ]);
    }
}
EOF

echo "✅ Contrôleur créé: app/Http/Controllers/Api/${FILE_NAME}_controller.php"

# Ajouter les routes dans api.php
CONTROLLER_NAME="${FILE_NAME^}Controller"
echo "" >> routes/api.php
echo "// Routes pour $API_NAME" >> routes/api.php
echo "Route::prefix('livreur')->middleware('auth:livreur')->group(function () {" >> routes/api.php
echo "    Route::get('$FILE_NAME', [${CONTROLLER_NAME}::class, 'index']);" >> routes/api.php
echo "    Route::get('$FILE_NAME/{id}', [${CONTROLLER_NAME}::class, 'show']);" >> routes/api.php
echo "});" >> routes/api.php

echo "✅ Routes ajoutées dans routes/api.php"

# Mettre à jour la documentation Swagger
echo "🔄 Mise à jour de la documentation Swagger..."
/opt/homebrew/bin/php artisan l5-swagger:generate

if [ $? -eq 0 ]; then
    echo "✅ Documentation Swagger mise à jour!"
    echo ""
    echo "🎯 Prochaines étapes:"
    echo "   1. Implémentez la logique dans le contrôleur"
    echo "   2. Testez l'API sur: http://127.0.0.1:8000/api/documentation"
    echo "   3. Ajoutez d'autres endpoints si nécessaire"
    echo ""
    echo "📁 Fichiers créés/modifiés:"
    echo "   - app/Http/Controllers/Api/${FILE_NAME}_controller.php"
    echo "   - routes/api.php"
    echo "   - Documentation Swagger mise à jour"
else
    echo "❌ Erreur lors de la mise à jour de Swagger"
    exit 1
fi
