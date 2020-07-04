#!/bin/bash
php artisan down
git pull
#mkdir public/static/storage
#php artisan storage:link
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan cache:clear
php artisan migrate
yarnpkg run production
php artisan up
