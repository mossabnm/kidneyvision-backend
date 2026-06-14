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

# Create required Laravel directories
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port (Railway sets PORT dynamically)
EXPOSE ${PORT:-8000}

# Start Laravel
CMD php artisan migrate --force 2>/dev/null; php artisan config:cache; php artisan route:cache; php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
