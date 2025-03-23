# Use official PHP image
FROM php:8.2-fpm

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libssl-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Install MongoDB extension
RUN pecl install mongodb && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel files
COPY . .

# ✅ Install dependencies **before** running artisan commands
RUN composer install --no-dev --optimize-autoloader

# ✅ Only now clear config & cache
RUN php artisan config:clear
RUN php artisan cache:clear

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
