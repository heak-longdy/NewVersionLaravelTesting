#!/bin/sh
set -e

# Cache configurations if APP_KEY is available
if [ -n "$APP_KEY" ]; then
    echo ">> Caching Laravel configuration, routes, and views..."
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run database migrations if AUTO_MIGRATE is true (default: true)
if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    echo ">> Running database migrations on Supabase..."
    php artisan migrate --force --no-interaction || echo "Migration failed or database not ready, continuing..."
fi

echo ">> Starting application server..."
exec "$@"
