#!/bin/bash
set -e

echo "========================================"
echo " Worship IO API - Iniciando contenedor"
echo "========================================"

# -------------------------------------------------------
# 1. Verificar que APP_KEY esté definida
# -------------------------------------------------------
if [ -z "$APP_KEY" ]; then
    echo "[WARNING] La variable APP_KEY no está definida."
    echo "          Se generará una temporal para que el build/check no falle,"
    echo "          pero asegúrate de configurarla en Coolify para persistencia."
    php artisan key:generate --show --no-interaction > /tmp/temp_key
    # Solo para que no falle el inicio, pero lo ideal es configurarla
fi

# Esperamos un momento para que otros servicios (DB) arranquen
sleep 5

# -------------------------------------------------------
# 2. Preparar configuración y migraciones
# -------------------------------------------------------
echo "[1/5] Preparando configuración..."
php artisan config:clear --no-interaction

echo "[2/5] Ejecutando migraciones..."
# Usamos un bucle de espera para que la base de datos sea realmente accesible
# antes de intentar migrar, evitando fallos prematuros en Coolify.
for i in {1..30}; do
    if php artisan db:show > /dev/null 2>&1; then
        echo "Base de datos accesible..."
        break
    fi
    echo "Esperando por la base de datos (intento $i/30)..."
    sleep 2
done

# Ejecutamos las migraciones con --force para producción
php artisan migrate --force --no-interaction

# -------------------------------------------------------
# 3. Limpiar cachés obsoletas y optimizar
# -------------------------------------------------------
echo "[3/5] Limpiando cachés y optimizando..."
# cache:clear puede fallar si la tabla cache no se creó correctamente
# por eso lo envolvemos para evitar que detenga el arranque si no es crítico.
php artisan cache:clear --no-interaction || true
php artisan optimize --no-interaction

# 4. (Omitido) Publicar assets de Horizon
# php artisan vendor:publish --tag=horizon-assets --force

# -------------------------------------------------------
# 6. Iniciar Supervisor (Nginx + PHP-FPM + Horizon + Reverb)
# -------------------------------------------------------
echo "[5/5] Iniciando servicios con Supervisor..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
