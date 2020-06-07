#!/bin/bash
mkdir public/static
git pull
php artisan down
composer install --optimize-autoloader --no-dev
yarnpkg run production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan cache:clear
php artisan migrate
php artisan storage:link
php artisan up
