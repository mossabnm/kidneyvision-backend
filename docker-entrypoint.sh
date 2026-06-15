#!/bin/bash

# Configure Apache port
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

# Enable Apache modules
a2dismod mpm_event mpm_worker || true
a2enmod mpm_prefork || true

# Laravel permissions
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 777 storage bootstrap/cache

# Laravel commands (do not crash container)
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan migrate --force || true

echo "Starting Apache on port ${PORT}"

exec apache2-foreground