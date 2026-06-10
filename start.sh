#!/bin/bash
cd /var/www
php artisan config:clear
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php-fpm &
nginx -g "daemon off;"