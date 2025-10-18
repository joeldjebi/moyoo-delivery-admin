#!/bin/bash

# Script de déploiement pour MOYOO Admin Delivery
# Usage: ./deploy.sh [environment]

set -e  # Arrêter en cas d'erreur

ENVIRONMENT=${1:-production}
PROJECT_NAME="moyoo-delivery-admin"

echo "🚀 Déploiement de $PROJECT_NAME en environnement: $ENVIRONMENT"
echo "=================================================="

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages colorés
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "artisan" ]; then
    log_error "Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

log_info "Vérification de l'environnement..."

# Vérifier les fichiers de configuration
if [ ! -f ".env" ]; then
    log_error "Fichier .env manquant"
    exit 1
fi

if [ ! -f ".env.$ENVIRONMENT" ]; then
    log_warning "Fichier .env.$ENVIRONMENT non trouvé, utilisation de .env"
fi

# Étapes de déploiement
log_info "1. Mise à jour des dépendances Composer..."
composer install --no-dev --optimize-autoloader

log_info "2. Mise à jour des dépendances NPM..."
npm ci --production

log_info "3. Compilation des assets..."
npm run build

log_info "4. Mise à jour de la base de données..."
php artisan migrate --force

log_info "5. Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

log_info "6. Génération de la documentation Swagger..."
php artisan l5-swagger:generate

log_info "7. Nettoyage du cache..."
php artisan cache:clear

log_info "8. Vérification des permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

log_success "Déploiement terminé avec succès!"
log_info "L'application est maintenant disponible en environnement: $ENVIRONMENT"

# Afficher les informations utiles
echo ""
echo "📋 Informations utiles:"
echo "- URL: $(php artisan config:show app.url 2>/dev/null || echo 'Non configuré')"
echo "- Environnement: $(php artisan config:show app.env 2>/dev/null || echo 'Non configuré')"
echo "- Debug: $(php artisan config:show app.debug 2>/dev/null || echo 'Non configuré')"
echo ""
echo "🔧 Commandes utiles:"
echo "- Voir les logs: tail -f storage/logs/laravel.log"
echo "- Tester l'application: php artisan serve"
echo "- Vérifier les routes: php artisan route:list"
echo ""
