# ============================================================
# Stage 1: Dependencias de PHP con Composer (sin dev)
# ============================================================
FROM composer:2.8 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


# ============================================================
# Stage 2: Imagen final de producción
# ============================================================
FROM php:8.3-fpm-alpine AS production

LABEL maintainer="worship-io-api"
LABEL description="Laravel 13 API - Worship IO"

# Dependencias del sistema
RUN apk add --no-cache \
    bash \
    curl \
    nginx \
    supervisor \
    shadow \
    # Librerías requeridas por extensiones PHP
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev \
    openssl-dev

# -------------------------------------------------------
# Extensiones PHP requeridas por Laravel 13
# -------------------------------------------------------
# Nativas de la imagen base (no requieren instalación):
#   ctype, fileinfo, filter, hash, openssl, pcre, session, tokenizer
#
# Incluidas en php:8.3-fpm-alpine por defecto:
#   opcache, phar
#
# Las siguientes sí requieren instalación:
RUN docker-php-ext-install -j$(nproc) \
    # Requeridas por Laravel
    pdo \
    pdo_mysql \
    mbstring \
    dom \
    xml \
    tokenizer \
    bcmath \
    pcntl \
    # Extras para el proyecto (Horizon, Reverb, queues)
    zip \
    intl \
    opcache \
    sockets

# Extensión cURL (viene incluida en la imagen base, solo habilitamos)
RUN docker-php-ext-enable opcache

# Extensión phpredis (para Redis / Horizon / Cache)
RUN apk add --no-cache --virtual .build-deps autoconf g++ make linux-headers && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    apk del .build-deps

# Configuraciones PHP y servidor
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
# Pool de PHP-FPM con socket Unix (igual que docs oficiales de Laravel)
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# Crear directorio para el socket Unix de PHP-FPM
# (debe existir antes de que PHP-FPM arranque)
RUN mkdir -p /var/run/php

# Usuario no-root para mayor seguridad
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup

WORKDIR /var/www/html

# Copiar vendors desde el stage de Composer
COPY --from=composer-builder /app/vendor ./vendor

# Copiar el código fuente de la aplicación
COPY --chown=appuser:appgroup . .

# Permisos de directorios críticos de Laravel
RUN chown -R appuser:appgroup /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache && \
    chown -R appuser:appgroup /var/log/nginx && \
    chown -R appuser:appgroup /var/lib/nginx && \
    chown -R appuser:appgroup /run/nginx && \
    # Directorio del socket Unix de PHP-FPM
    chown -R appuser:appgroup /var/run/php

# Puerto HTTP (Nginx)
EXPOSE 80

# Puerto WebSockets (Laravel Reverb)
EXPOSE 8080

USER appuser

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
