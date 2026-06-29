#!/bin/sh
set -e

# Run database setup only when explicitly requested.
# This prevents php-fpm startup failures (and 502 from nginx) when DB is not ready yet.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"
