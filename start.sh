#!/bin/bash
cd /var/www
php artisan config:clear
php artisan migrate --force
rm -f public/storage
php artisan storage:link
php-fpm &
nginx -g "daemon off;"