#!/bin/bash

# Exécute pgloader en lisant les variables d'environnement ci-dessous
# Usage:
#   MYSQL_USER=root \
#   MYSQL_PASS=secret \
#   MYSQL_DB=moyoo \
#   PG_USER=postgres \
#   PG_PASS=root \
#   PG_DB=admin_delivery \
#   PG_HOST=127.0.0.1 \
#   MYSQL_HOST=127.0.0.1 \
#   bash scripts/run-pgloader.sh

set -e

if ! command -v pgloader >/dev/null 2>&1; then
  echo "❌ pgloader n'est pas installé. macOS: brew install pgloader" >&2
  exit 1
fi

MYSQL_HOST=${MYSQL_HOST:-127.0.0.1}
MYSQL_PORT=${MYSQL_PORT:-3306}
PG_HOST=${PG_HOST:-127.0.0.1}
PG_PORT=${PG_PORT:-5432}

if [ -z "$MYSQL_USER" ] || [ -z "$MYSQL_PASS" ] || [ -z "$MYSQL_DB" ]; then
  echo "❌ Variables MySQL requises: MYSQL_USER, MYSQL_PASS, MYSQL_DB" >&2
  exit 1
fi

if [ -z "$PG_USER" ] || [ -z "$PG_PASS" ] || [ -z "$PG_DB" ]; then
  echo "❌ Variables PostgreSQL requises: PG_USER, PG_PASS, PG_DB" >&2
  exit 1
fi

MYSQL_URI="mysql://${MYSQL_USER}:${MYSQL_PASS}@${MYSQL_HOST}:${MYSQL_PORT}/${MYSQL_DB}"
PG_URI="postgresql://${PG_USER}:${PG_PASS}@${PG_HOST}:${PG_PORT}/${PG_DB}"

echo "🚚 Migration des données: ${MYSQL_URI}  ->  ${PG_URI}"
pgloader ${MYSQL_URI} ${PG_URI}

echo "✅ Migration terminée"


