#!/usr/bin/env sh

cd /var/www
php /var/www/artisan migrate
exec php-fpm


