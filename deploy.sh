#!/bin/bash

# Abort on the first failing step so a broken install or build never reaches `php artisan up`
set -euo pipefail

on_failure() {
    echo "Deploy failed at line $1. The app is still in maintenance mode; fix the issue and re-run, or run 'php artisan up'." >&2
}
trap 'on_failure $LINENO' ERR

# Turn on maintenance mode
php artisan down

# Reset permissions recursively for the storage directory
# From time to time the reset cache command fails because of this
sudo chmod -R 0777 storage/

# Pull the latest changes, then re-run this script from the top so the
# freshly pulled version is executed (bash reads scripts lazily, so a
# script modified mid-run would otherwise execute from shifted offsets)
if [ "${DEPLOY_PULLED:-0}" != "1" ]; then
    git pull origin master
    DEPLOY_PULLED=1 exec "$0" "$@"
fi

# Get latest Composer
sh ./getcomposer.sh

# Move Composer to $PATH
sudo mv composer.phar /usr/local/bin/composer

# Install/update composer dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Run database migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear

# Clear expired password reset tokens
php artisan auth:clear-resets

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Gracefully restart the Reverb websocket server; its process manager brings it back up
php artisan reverb:restart

# Generate assetlinks
php artisan generate:assetlinks

# Install node modules only when the lockfile changed since the last install.
# npm ci wipes node_modules and reinstalls everything, which can starve the server
# of I/O and memory; the hash lives inside node_modules so a wipe invalidates it too.
lock_hash_file=node_modules/.package-lock.sha
current_lock_hash=$(sha256sum package-lock.json | cut -d' ' -f1)
if [ ! -f "$lock_hash_file" ] || [ "$(cat "$lock_hash_file")" != "$current_lock_hash" ]; then
    nice -n 19 ionice -c3 npm ci --no-audit --no-fund
    echo "$current_lock_hash" > "$lock_hash_file"
fi

# Build client assets using Vite at low priority with a capped Node heap so the build
# can't push php-fpm, MariaDB or SSR into swap or the OOM killer.
# Override with DEPLOY_NODE_MAX_MEMORY_MB if the build needs more headroom.
NODE_OPTIONS="--max-old-space-size=${DEPLOY_NODE_MAX_MEMORY_MB:-1024}" nice -n 19 ionice -c3 npm run build

# SSR is opt-in via INERTIA_SSR_ENABLED in .env (read from the config cached above).
# It is off on the current 2GB server because the SSR process alone takes ~1GB.
if php artisan config:show inertia.ssr.enabled | grep -q 'true'; then
    NODE_OPTIONS="--max-old-space-size=${DEPLOY_NODE_MAX_MEMORY_MB:-1024}" nice -n 19 ionice -c3 npm run build:ssr

    # Restart SSR server so it picks up the new bundle; the process manager brings it back up.
    # stop-ssr exits non-zero when the server isn't running, which is fine here.
    php artisan inertia:stop-ssr || true

    # Wait for the SSR server to come back; the app falls back to client-side rendering without it
    ssr_is_up=false
    for _ in $(seq 1 10); do
        sleep 1
        if php artisan inertia:check-ssr > /dev/null 2>&1; then
            ssr_is_up=true
            break
        fi
    done

    if [ "$ssr_is_up" = true ]; then
        echo "Inertia SSR server is running."
    else
        echo "WARNING: Inertia SSR server did not come back up; check its process manager and storage/logs/laravel.log." >&2
    fi
fi

# Turn off maintenance mode
php artisan up
