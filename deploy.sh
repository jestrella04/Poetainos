#!/bin/bash

# Turn on maintenance mode
php artisan down

# Reset permissions recursively for the storage directory
# From time to time the reset cache command fails because of this
chmod -R 0777 storage/

# Pull the latest changes from the git repository
# git reset --hard
# git clean -df
git pull origin master

# Install/update composer dependecies
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

# Restart service worker
php artisan queue:restart

# Install node modules
npm install

# Build assets using Laravel Mix
npm run production

# Turn off maintenance mode
php artisan up
