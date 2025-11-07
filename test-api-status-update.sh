#!/bin/bash

# Script de test pour vérifier que l'API met à jour correctement le statut des colis

BASE_URL="http://192.168.1.8:8000"
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTkyLjE2OC4xLjY6ODAwMC9hcGkvbGl2cmV1ci9sb2dpbiIsImlhdCI6MTc2MjQyMTAwNywiZXhwIjoxNzYyNTkzODA3LCJuYmYiOjE3NjI0MjEwMDcsImp0aSI6IjJTM0RyR0pZQVMyUVllcDAiLCJzdWIiOiIxIiwicHJ2IjoiNWZhYWY0NzcxYzNkNThlMzI5MzFhNzQwOGY5MzdiYTkzMzYzYjNjOSIsInR5cGUiOiJsaXZyZXVyIiwiZW50cmVwcmlzZV9pZCI6MSwic3RhdHVzIjoiYWN0aWYifQ.ZYlYbfVbp7kD0q29nQ_vg1dXv_3oLnuW4gUj2yGSskU"

echo "🧪 Test de mise à jour du statut via l'API"
echo "=========================================="
echo ""

# 1. Récupérer la liste des colis assignés AVANT la mise à jour
echo "📋 Étape 1: Récupération de la liste des colis assignés..."
echo ""

RESPONSE_BEFORE=$(curl -s -X 'GET' \
  "${BASE_URL}/api/livreur/colis-assignes" \
  -H 'accept: application/json' \
  -H "Authorization: Bearer ${TOKEN}")

echo "Réponse reçue:"
echo "$RESPONSE_BEFORE" | jq '.' 2>/dev/null || echo "$RESPONSE_BEFORE"
echo ""

# Extraire le statut du premier colis (si disponible)
COLIS_ID=$(echo "$RESPONSE_BEFORE" | jq -r '.data[0].id // empty' 2>/dev/null)
COLIS_STATUS_BEFORE=$(echo "$RESPONSE_BEFORE" | jq -r '.data[0].status // empty' 2>/dev/null)

if [ -z "$COLIS_ID" ]; then
    echo "❌ Aucun colis trouvé dans la liste."
    exit 1
fi

echo "📦 Colis trouvé:"
echo "   - ID: $COLIS_ID"
echo "   - Statut actuel: $COLIS_STATUS_BEFORE"
echo ""

# Vérifier si le colis est en cours (status = 1)
if [ "$COLIS_STATUS_BEFORE" != "1" ]; then
    echo "⚠️  Le colis n'est pas en cours (status = 1)."
    echo "   Statut actuel: $COLIS_STATUS_BEFORE"
    echo "   Le colis doit être en cours pour être complété."
    echo ""
    echo "💡 Pour tester, démarrez d'abord une livraison avec:"
    echo "   curl -X 'POST' '${BASE_URL}/api/livreur/colis/${COLIS_ID}/start-delivery' \\"
    echo "     -H 'accept: application/json' \\"
    echo "     -H \"Authorization: Bearer ${TOKEN}\""
    exit 1
fi

# 2. Compléter la livraison
echo "🔄 Étape 2: Complétion de la livraison..."
echo ""

# Créer un fichier temporaire pour la photo (si nécessaire)
PHOTO_PATH="/tmp/test-photo.jpg"
if [ ! -f "$PHOTO_PATH" ]; then
    # Créer une image de test simple (1x1 pixel PNG)
    echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > "$PHOTO_PATH" 2>/dev/null || touch "$PHOTO_PATH"
fi

RESPONSE_COMPLETE=$(curl -s -X 'POST' \
  "${BASE_URL}/api/livreur/colis/${COLIS_ID}/complete-delivery" \
  -H 'accept: application/json' \
  -H "Authorization: Bearer ${TOKEN}" \
  -H 'Content-Type: multipart/form-data' \
  -F "code_validation=12345" \
  -F "photo_proof=@${PHOTO_PATH}" \
  -F "note_livraison=Test de mise à jour du statut" \
  -F "latitude=5.359952" \
  -F "longitude=-4.008256")

echo "Réponse de complétion:"
echo "$RESPONSE_COMPLETE" | jq '.' 2>/dev/null || echo "$RESPONSE_COMPLETE"
echo ""

# Vérifier le succès
SUCCESS=$(echo "$RESPONSE_COMPLETE" | jq -r '.success // false' 2>/dev/null)
STATUS_IN_RESPONSE=$(echo "$RESPONSE_COMPLETE" | jq -r '.data.status // empty' 2>/dev/null)

if [ "$SUCCESS" != "true" ]; then
    echo "❌ La complétion de la livraison a échoué."
    echo "$RESPONSE_COMPLETE"
    exit 1
fi

echo "✅ Livraison complétée avec succès!"
echo "   - Statut dans la réponse: $STATUS_IN_RESPONSE"
echo ""

# Attendre un peu pour que la base de données soit mise à jour
echo "⏳ Attente de 2 secondes pour la mise à jour de la base de données..."
sleep 2
echo ""

# 3. Récupérer la liste des colis assignés APRÈS la mise à jour
echo "📋 Étape 3: Vérification de la liste des colis assignés après mise à jour..."
echo ""

RESPONSE_AFTER=$(curl -s -X 'GET' \
  "${BASE_URL}/api/livreur/colis-assignes" \
  -H 'accept: application/json' \
  -H "Authorization: Bearer ${TOKEN}")

# Extraire le statut du colis mis à jour
COLIS_STATUS_AFTER=$(echo "$RESPONSE_AFTER" | jq -r ".data[] | select(.id == ${COLIS_ID}) | .status" 2>/dev/null)

echo "📊 Résultats:"
echo "   - Statut AVANT: $COLIS_STATUS_BEFORE"
echo "   - Statut APRÈS: $COLIS_STATUS_AFTER"
echo "   - Statut attendu: 2 (Livré)"
echo ""

# Vérifier que le statut est bien mis à jour
if [ "$COLIS_STATUS_AFTER" = "2" ]; then
    echo "✅ SUCCÈS: Le statut a été correctement mis à jour à 2 (Livré)!"
    echo ""
    echo "📈 Statistiques:"
    STATS=$(echo "$RESPONSE_AFTER" | jq '.statistiques' 2>/dev/null)
    echo "$STATS" | jq '.' 2>/dev/null || echo "$STATS"
    echo ""
    echo "✅ Tous les tests sont passés avec succès!"
else
    echo "❌ ÉCHEC: Le statut n'a pas été mis à jour correctement!"
    echo "   Statut actuel: $COLIS_STATUS_AFTER"
    echo "   Statut attendu: 2"
    echo ""
    echo "Réponse complète:"
    echo "$RESPONSE_AFTER" | jq '.' 2>/dev/null || echo "$RESPONSE_AFTER"
    exit 1
fi

# Nettoyer le fichier temporaire
rm -f "$PHOTO_PATH"

echo ""
echo "🎉 Test terminé avec succès!"

