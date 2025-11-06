#!/bin/bash

# Script pour configurer l'URL de base de Swagger
# Usage: ./scripts/configure-swagger-url.sh [URL]

# URL par défaut
DEFAULT_URL="http://192.168.1.6:8000"

# Utiliser l'URL fournie en paramètre ou l'URL par défaut
SWAGGER_URL=${1:-$DEFAULT_URL}

echo "🔧 Configuration de l'URL Swagger: $SWAGGER_URL"

# Vérifier si le fichier .env existe
if [ ! -f .env ]; then
    echo "⚠️  Fichier .env non trouvé. Création d'un fichier .env basique..."
    cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:$(php artisan key:generate --show)
APP_DEBUG=true
APP_URL=$SWAGGER_URL

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/Users/macbookpro/Documents/MOYOO/admin-delivery/database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuration Swagger
L5_SWAGGER_BASE_PATH=$SWAGGER_URL
L5_SWAGGER_CONST_HOST=$SWAGGER_URL
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_USE_ABSOLUTE_PATH=true
EOF
    echo "✅ Fichier .env créé avec l'URL: $SWAGGER_URL"
else
    echo "📝 Mise à jour du fichier .env existant..."

    # Mettre à jour APP_URL
    if grep -q "APP_URL=" .env; then
        sed -i.bak "s|APP_URL=.*|APP_URL=$SWAGGER_URL|" .env
    else
        echo "APP_URL=$SWAGGER_URL" >> .env
    fi

    # Mettre à jour L5_SWAGGER_BASE_PATH
    if grep -q "L5_SWAGGER_BASE_PATH=" .env; then
        sed -i.bak "s|L5_SWAGGER_BASE_PATH=.*|L5_SWAGGER_BASE_PATH=$SWAGGER_URL|" .env
    else
        echo "L5_SWAGGER_BASE_PATH=$SWAGGER_URL" >> .env
    fi

    # Mettre à jour L5_SWAGGER_CONST_HOST
    if grep -q "L5_SWAGGER_CONST_HOST=" .env; then
        sed -i.bak "s|L5_SWAGGER_CONST_HOST=.*|L5_SWAGGER_CONST_HOST=$SWAGGER_URL|" .env
    else
        echo "L5_SWAGGER_CONST_HOST=$SWAGGER_URL" >> .env
    fi

    # Ajouter les autres variables Swagger si elles n'existent pas
    if ! grep -q "L5_SWAGGER_GENERATE_ALWAYS=" .env; then
        echo "L5_SWAGGER_GENERATE_ALWAYS=true" >> .env
    fi

    if ! grep -q "L5_SWAGGER_USE_ABSOLUTE_PATH=" .env; then
        echo "L5_SWAGGER_USE_ABSOLUTE_PATH=true" >> .env
    fi

    echo "✅ Fichier .env mis à jour avec l'URL: $SWAGGER_URL"
fi

# Régénérer la documentation Swagger
echo "🔄 Régénération de la documentation Swagger..."
php artisan l5-swagger:generate

echo "✅ Configuration terminée!"
echo "📖 Documentation Swagger disponible à: $SWAGGER_URL/api/documentation"
echo ""
echo "💡 Pour changer l'URL plus tard, utilisez:"
echo "   ./scripts/configure-swagger-url.sh http://votre-nouvelle-url:port"
