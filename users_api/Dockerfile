FROM php:8.2-fpm

# Install dependencies, including Composer and Symfony
RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    mariadb-client \
    curl \
    jq \
    libsodium-dev \ 
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd sodium \ 
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony
# sodium needed for lexik auth bundle

# Set working directory
WORKDIR /var/www/html/users_api

# Copy application files
COPY . /var/www/html/users_api/

# Install PHP dependencies using Composer (with optimization for production)
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Set permissions for Symfony
RUN chown -R www-data:www-data /var/www/html/users_api/var /var/www/html/users_api/public

EXPOSE 8000

# Use PHP built-in server for local testing
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

