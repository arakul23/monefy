#!/bin/sh
set -e

if [ ! -f ".env" ]; then
    echo ".env not found. Copying from .env.example..."
    cp .env.example .env
fi

if [ ! -d "vendor" ]; then
    echo "Vendor folder is empty. Installing dependencies..."
    composer install --no-interaction --optimize-autoloader
else
    echo "Vendor folder exists. Syncing..."
    composer install --no-interaction --optimize-autoloader
fi

if [ -z "$(grep '^APP_KEY=.\+' .env)" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"
