# ============================================================
# Stage 1: Dependencias de PHP con Composer
# ============================================================
FROM composer:2.8 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
# Ignoramos requisitos de plataforma aquí porque se instalan en el Stage 2
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs


# ============================================================
# Stage 2: Imagen final de producción
# ============================================================
FROM php:8.4-fpm-alpine AS production

LABEL maintainer="worship-io-api"
LABEL description="Laravel 13 API - Worship IO"

# Instalamos herramientas básicas y el instalador de extensiones de PHP
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache bash curl nginx supervisor shadow

# -------------------------------------------------------
# Instalación de extensiones PHP
# -------------------------------------------------------
# mbstring y opcache suelen venir ya en la imagen base alpine, 
# pero instalamos las necesarias para el proyecto.
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    pcntl \
    zip \
    intl \
    sockets \
    redis

# Configuraciones PHP y servidor
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
# Limpiamos configuraciones por defecto para evitar conflictos
RUN rm -rf /etc/nginx/http.d/* /etc/nginx/conf.d/*
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh && \
    mkdir -p /var/run/php

# Usuario no-root
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup

WORKDIR /var/www/html

# Copiar vendors y código
COPY --from=composer-builder /app/vendor ./vendor
COPY --chown=appuser:appgroup . .

# Permisos y directorios base
RUN mkdir -p /var/run/php /var/log/nginx /var/lib/nginx /run/nginx /var/tmp/nginx && \
    chown -R appuser:appgroup /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache && \
    # Unimos a nginx al grupo appgroup para que acceda al socket y archivos si es necesario
    addgroup nginx appgroup && \
    # Devolvemos la propiedad de los directorios de nginx al usuario nginx
    chown -R nginx:nginx /var/log/nginx /var/lib/nginx /run/nginx /var/tmp/nginx /var/run/php && \
    chmod -R 775 /var/run/php

EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
