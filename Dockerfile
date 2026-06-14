FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Remove local .env so Railway env vars are the ONLY source of truth
RUN rm -f .env

# Create required Laravel directories
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Start: migrate then serve (NO config:cache to avoid freezing wrong values)
CMD php artisan migrate --force 2>/dev/null; php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
