#!/usr/bin/env sh
set -eu

mkdir -p \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/testing \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

if [ ! -L /var/www/html/public/storage ] && [ ! -e /var/www/html/public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
