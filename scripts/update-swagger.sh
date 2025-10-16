#!/bin/bash

# Script pour mettre à jour automatiquement la documentation Swagger
# Usage: ./scripts/update-swagger.sh

echo "🔄 Mise à jour de la documentation Swagger..."

# Aller dans le répertoire du projet
cd "$(dirname "$0")/.."

# Générer la documentation Swagger
echo "📝 Génération de la documentation..."
php artisan l5-swagger:generate

# Vérifier si la génération a réussi
if [ $? -eq 0 ]; then
    echo "✅ Documentation Swagger mise à jour avec succès!"
    echo "🌐 Accédez à la documentation sur: http://127.0.0.1:8000/api/documentation"
else
    echo "❌ Erreur lors de la génération de la documentation"
    exit 1
fi

echo "📋 Prochaines étapes:"
echo "   1. Vérifiez la documentation sur http://127.0.0.1:8000/api/documentation"
echo "   2. Testez les endpoints directement depuis l'interface Swagger"
echo "   3. Ajoutez de nouvelles annotations @OA pour les nouveaux endpoints"
