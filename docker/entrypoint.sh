#!/usr/bin/env bash
# Container entrypoint — gira PRIMA che nginx/php-fpm accettino traffico.
# Le migrazioni devono completare qui, altrimenti la prima request crasha
# perché CACHE_STORE/SESSION_DRIVER=database leggono tabelle inesistenti.
set -e

cd /var/www/html

echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

echo "[entrypoint] Linking storage..."
php artisan storage:link --force --no-interaction || true

echo "[entrypoint] Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
