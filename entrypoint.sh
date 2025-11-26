#!/bin/sh
set -e

# Fixer les permissions des répertoires storage et cache
echo "Configuration des permissions..."
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Attendre que PostgreSQL soit prêt
echo "Attente de la base de données PostgreSQL..."
until pg_isready -h "$DB_HOST" -U "$DB_USERNAME" -p "$DB_PORT"; do
  >&2 echo "PostgreSQL n'est pas encore disponible - attente..."
  sleep 1
done

>&2 echo "PostgreSQL est prêt!"

# Exécuter les migrations
echo "Exécution des migrations..."
# php /app/artisan migrate --force

# Exécuter les seeders (optionnel)
# php /app/artisan db:seed

echo "Démarrage de Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
