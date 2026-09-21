#!/bin/sh
set -e
cd /var/www/html

# Storage is a volume, so make sure the folder structure exists
mkdir -p storage/app/public storage/framework/cache/data \
         storage/framework/sessions storage/framework/views \
         storage/logs bootstrap/cache

php artisan package:discover --ansi || true

# If APP_KEY is not set, generate one once and keep it in the storage volume
if [ -z "$APP_KEY" ]; then
  if [ ! -f storage/.app_key ]; then
    php artisan key:generate --show > storage/.app_key
  fi
  export APP_KEY="$(cat storage/.app_key)"
fi

# Only the main app container runs migrations
if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force
fi

# Cache config/routes/views in production
if [ "$APP_ENV" = "production" ]; then
  php artisan config:cache
  php artisan route:cache || true
  php artisan view:cache || true
fi

chown -R www-data:www-data storage bootstrap/cache

# PHP-FPM manages its own users; other commands (queue, scheduler) run as www-data
if [ "$1" = "php-fpm" ]; then
  exec "$@"
else
  exec su-exec www-data "$@"
fi
