FROM php:8.2-fpm

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Install Node.js dependencies and build assets
RUN npm ci && npm run build

# Set permissions
RUN chown -R www-data:www-data /app

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
