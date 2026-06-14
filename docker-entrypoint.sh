#!/bin/bash
set -e

# Set Apache to listen on Railway's dynamic PORT
sed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT:-8080}/" /etc/apache2/sites-available/000-default.conf

# Create storage dirs if missing
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Start Apache
exec apache2-foreground
