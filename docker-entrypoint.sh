#!/bin/bash
set -e

# Configure Apache to listen on the correct PORT provided by Railway (defaulting to 80)
# This is critical for Railway routing!
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

# Ensure conflicting MPM modules are disabled at runtime just in case
a2dismod mpm_event mpm_worker || true
a2enmod mpm_prefork || true

# Create storage dirs if missing and set permissions
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Clear/Optimize Laravel Configuration for production stability
php artisan config:clear
php artisan cache:clear

echo "Starting Apache server on port ${PORT}..."
exec apache2-foreground
