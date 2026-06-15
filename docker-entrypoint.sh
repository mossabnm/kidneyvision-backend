#!/bin/bash
set -e

# Create storage dirs if missing
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Clear/Optimize Laravel Configuration for production stability
php artisan config:clear
php artisan cache:clear

# Start PHP built-in web server on the port provided by Railway (defaulting to 80)
echo "Starting Laravel server on port ${PORT:-80}..."
exec php -S 0.0.0.0:${PORT:-80} -t public server.php
