#!/bin/bash
php artisan down
git pull
composer install --optimize-autoloader --no-dev
yarnpkg install
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan cache:clear
php artisan migrate --force
yarnpkg run production
php artisan up
