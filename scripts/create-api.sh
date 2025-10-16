#!/bin/bash

# Script simple pour créer une nouvelle API avec documentation Swagger
# Usage: ./scripts/create-api.sh "nom_api" "Description de l'API"

API_NAME="$1"
API_DESCRIPTION="$2"

if [ -z "$API_NAME" ] || [ -z "$API_DESCRIPTION" ]; then
    echo "❌ Usage: $0 \"nom_api\" \"Description de l'API\""
    echo "   Exemple: $0 \"colis-assignes\" \"Récupérer les colis assignés au livreur\""
    exit 1
fi

echo "🚀 Création d'une nouvelle API: $API_NAME"
echo "📝 Description: $API_DESCRIPTION"

# Aller dans le répertoire du projet
cd "$(dirname "$0")/.."

# Créer le nom de classe (PascalCase)
CLASS_NAME=$(echo "$API_NAME" | sed 's/-\([a-z]\)/\U\1/g' | sed 's/^\([a-z]\)/\U\1/' | sed 's/-//g')
CONTROLLER_NAME="${CLASS_NAME}Controller"

echo "📁 Nom du contrôleur: $CONTROLLER_NAME"

# Créer le contrôleur
cat > "app/Http/Controllers/Api/${CONTROLLER_NAME}.php" << EOF
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="$API_DESCRIPTION",
 *     description="$API_DESCRIPTION"
 * )
 */
class $CONTROLLER_NAME extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/livreur/$API_NAME",
     *     summary="Liste des $API_DESCRIPTION",
     *     description="$API_DESCRIPTION",
     *     tags={"$API_DESCRIPTION"},
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
}
EOF

echo "✅ Contrôleur créé: app/Http/Controllers/Api/${CONTROLLER_NAME}.php"

# Ajouter les routes dans api.php
echo "" >> routes/api.php
echo "// Routes pour $API_DESCRIPTION" >> routes/api.php
echo "Route::prefix('livreur')->middleware('auth:livreur')->group(function () {" >> routes/api.php
echo "    Route::get('$API_NAME', [${CONTROLLER_NAME}::class, 'index']);" >> routes/api.php
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
    echo "   - app/Http/Controllers/Api/${CONTROLLER_NAME}.php"
    echo "   - routes/api.php"
    echo "   - Documentation Swagger mise à jour"
else
    echo "❌ Erreur lors de la mise à jour de Swagger"
    exit 1
fi
