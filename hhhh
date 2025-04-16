# Use official PHP 8.2 with necessary extensions
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libssl-dev \
    libmongocrypt-dev \
    unzip \
    git \
    curl

# Install specific MongoDB extension version
RUN pecl install mongodb-1.16.2 \
    && docker-php-ext-enable mongodb

# Set working directory
WORKDIR /var/www/html

# Copy Laravel project files
COPY . .

# Install Composer dependencies with no scripts to avoid package discovery
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Copy environment file
COPY .env.production .env

# Run post-install commands manually
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose the necessary ports
EXPOSE 9000

# Start Laravel
CMD ["php-fpm"]
