# Stage 1: Builder PHP avec Composer et Node.js
FROM php:8.4-fpm-alpine3.21 AS builder
# Installation des dépendances système
RUN apk add --no-cache \
    curl \
    git \
    ca-certificates \
    nodejs \
    npm \
    postgresql-client \
    postgresql-dev \
    musl-dev \
    && rm -rf /var/cache/apk/*
# Installation des extensions PHP requises
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pdo_mysql
# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
WORKDIR /app
# Copie de tout le code source
COPY . .
# Installation des dépendances PHP (production)
RUN composer install
# Installation des dépendances Node et build des assets
RUN npm install && npm run build
# Stage 2: Runtime - Nginx + PHP-FPM
FROM php:8.4-fpm-alpine3.21
# Installation des dépendances runtime et de développement (temporaires)
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-client \
    postgresql-dev \
    musl-dev && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pdo_mysql && \
    echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/memory.ini && \
    apk del --no-cache postgresql-dev musl-dev
WORKDIR /app
# Copie du code compilé depuis le builder
COPY --from=builder /app .
# Création des répertoires nécessaires et permissions
RUN mkdir -p /var/log/supervisor \
    /app/storage/app \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache
# Configuration Nginx
COPY nginx.conf /etc/nginx/http.d/default.conf
# Configuration Supervisor
COPY supervisord.conf /etc/supervisord.conf
# Entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost || exit 1
EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
