#!/bin/bash

# Usage: ./scripts/configure-db-pgsql.sh
# Injecte la configuration PostgreSQL dans le fichier .env (créé si nécessaire)

set -e

DB_CONNECTION="pgsql"
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_DATABASE="admin_delivery"
DB_USERNAME="postgres"
DB_PASSWORD="root"

echo "🔧 Configuration base de données PostgreSQL dans .env"

if [ ! -f .env ]; then
  echo "⚠️  .env introuvable. Création d'un .env minimal..."
  cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:$(php artisan key:generate --show)
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=${DB_CONNECTION}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

EOF
  echo "✅ .env créé avec configuration PGSQL"
else
  echo "📝 Mise à jour du .env existant..."
  sed -i.bak "s|^DB_CONNECTION=.*|DB_CONNECTION=${DB_CONNECTION}|" .env || true
  if grep -q '^DB_CONNECTION=' .env; then :; else echo "DB_CONNECTION=${DB_CONNECTION}" >> .env; fi

  sed -i.bak "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env || true
  if grep -q '^DB_HOST=' .env; then :; else echo "DB_HOST=${DB_HOST}" >> .env; fi

  sed -i.bak "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env || true
  if grep -q '^DB_PORT=' .env; then :; else echo "DB_PORT=${DB_PORT}" >> .env; fi

  sed -i.bak "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env || true
  if grep -q '^DB_DATABASE=' .env; then :; else echo "DB_DATABASE=${DB_DATABASE}" >> .env; fi

  sed -i.bak "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env || true
  if grep -q '^DB_USERNAME=' .env; then :; else echo "DB_USERNAME=${DB_USERNAME}" >> .env; fi

  sed -i.bak "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env || true
  if grep -q '^DB_PASSWORD=' .env; then :; else echo "DB_PASSWORD=${DB_PASSWORD}" >> .env; fi
  echo "✅ .env mis à jour (PGSQL)"
fi

echo "🧹 Nettoyage des caches Laravel"
php artisan config:clear || true
php artisan cache:clear || true

echo "✅ Terminé."


