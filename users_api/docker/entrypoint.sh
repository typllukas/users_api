#!/bin/bash
set -e

echo "Installing PHP dependencies..."
composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader

echo "Setting correct permissions..."
chown -R www-data:www-data var public vendor

echo "Waiting for database to be ready..."
until php bin/console doctrine:database:exists --quiet; do
  sleep 2
done
echo "Database is ready!"

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Load fixtures only in non-production environments
if [ "$APP_ENV" != "prod" ]; then
    echo "Loading fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction
fi

# Allow additional commands to be executed (for docker-compose override)
exec "$@"

# Start PHP built-in server
echo "Starting application..."
exec php -S 0.0.0.0:8000 -t public
