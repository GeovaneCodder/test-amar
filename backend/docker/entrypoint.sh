#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/imports bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is empty. Set it in backend/.env before starting the stack."
    echo "Example: docker compose run --rm backend php artisan key:generate --force"
    exit 1
fi

if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force --graceful
    php artisan storage:link --force >/dev/null 2>&1 || true
fi

exec docker-php-entrypoint "$@"
