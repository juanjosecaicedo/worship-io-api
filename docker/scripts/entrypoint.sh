#!/bin/bash
set -e

echo "========================================"
echo " Worship IO API - Iniciando contenedor"
echo "========================================"

# -------------------------------------------------------
# 1. Verificar que APP_KEY esté definida
# -------------------------------------------------------
if [ -z "$APP_KEY" ]; then
    echo "[ERROR] La variable APP_KEY no está definida."
    echo "        Genera una con: php artisan key:generate --show"
    exit 1
fi

# -------------------------------------------------------
# 2. Limpiar cachés obsoletas antes de optimizar
# -------------------------------------------------------
echo "[1/5] Limpiando cachés..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# -------------------------------------------------------
# 3. Ejecutar migraciones pendientes
# -------------------------------------------------------
echo "[2/5] Ejecutando migraciones..."
php artisan migrate --force

# -------------------------------------------------------
# 4. Optimizar la app para producción
#    (combina config:cache, route:cache, event:cache)
# -------------------------------------------------------
echo "[3/5] Optimizando la aplicación..."
php artisan optimize

# -------------------------------------------------------
# 5. Publicar assets de Horizon (panel de control)
# -------------------------------------------------------
echo "[4/5] Publicando assets de Horizon..."
php artisan vendor:publish --tag=horizon-assets --force

# -------------------------------------------------------
# 6. Iniciar Supervisor (Nginx + PHP-FPM + Horizon + Reverb)
# -------------------------------------------------------
echo "[5/5] Iniciando servicios con Supervisor..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
