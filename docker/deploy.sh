#!/usr/bin/env bash
# Post-deploy hook — eseguito dentro il container `app` dopo che il health check passa.
#
# Configurare in Coolify → "Post-deployment command":
#   bash /var/www/html/docker/deploy.sh

set -euo pipefail

APP_DIR="/var/www/html"

log() {
    echo "[deploy] $(date -u '+%Y-%m-%d %H:%M:%S UTC') $*"
}

log "=== Deploy hooks start ==="
cd "$APP_DIR"

log "Migrations..."
php artisan migrate --force

log "Storage link..."
php artisan storage:link --force

log "Config cache..."
php artisan config:cache

log "Route cache..."
php artisan route:cache

log "View cache..."
php artisan view:cache

log "Event cache..."
php artisan event:cache

log "=== Deploy hooks complete ==="
