FROM php:8.2-cli

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Force Laravel to use Railway system environment variables
RUN rm -f .env

# Expose port (Railway usually uses 8080)
EXPOSE 8080

# Run Laravel directly without entrypoint or Apache
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
