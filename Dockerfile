# KORE ERP - Production Dockerfile
# Optimisé pour PHP 8.2-FPM avec Opcache

FROM php:8.2-fpm-alpine

# Définir l'argument de build pour l'environnement
ARG APP_ENV=production
ARG APP_DEBUG=false

# Variables d'environnement
ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=${APP_DEBUG} \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_REVALIDATE_FREQ=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_OPCACHE_INTERNED_STRINGS_BUFFER=16 \
    PHP_OPCACHE_FAST_SHUTDOWN=1 \
    PHP_OPCACHE_ENABLE_CLI=1 \
    PHP_MEMORY_LIMIT=512M \
    PHP_MAX_EXECUTION_TIME=300 \
    PHP_UPLOAD_MAX_FILESIZE=100M \
    PHP_POST_MAX_SIZE=100M \
    PHP_MAX_INPUT_VARS=3000

# Installation des dépendances système
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    sqlite \
    sqlite-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    autoconf \
    g++ \
    make \
    openssl-dev \
    linux-headers \
    && rm -rf /var/cache/apk/*

# 1. Installation des extensions PHP natives
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# 2. LA CORRECTION : Installation de Redis via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-kore-erp.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

# Configuration de Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/kore-erp.conf /etc/nginx/conf.d/default.conf

# Configuration de Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer l'utilisateur et le groupe pour l'application
RUN addgroup -g 1000 koreerp && \
    adduser -D -u 1000 -G koreerp koreerp && \
    chown -R koreerp:koreerp /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY --chown=koreerp:koreerp . .

# Installation des dépendances Composer AVEC --no-audit POUR CONTOURNER LE BLOCAGE DOCUSIGN
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-audit && \
    composer clear-cache

# Générer la clé d'application si elle n'existe pas
RUN php artisan key:generate --force || true

# Optimiser Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache

# Créer les répertoires nécessaires
RUN mkdir -p /var/www/html/storage/framework/{cache,sessions,testing,views} && \
    mkdir -p /var/www/html/storage/logs && \
    mkdir -p /var/www/html/bootstrap/cache && \
    chown -R koreerp:koreerp /var/www/html/storage && \
    chown -R koreerp:koreerp /var/www/html/bootstrap/cache

# Définir les permissions
RUN chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

# Passer à l'utilisateur non-root
USER koreerp

# Exposer le port
EXPOSE 80

# Commande de démarrage
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]