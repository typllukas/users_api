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
    librabbitmq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd sodium \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony \
    && rm -rf /var/lib/apt/lists/*  # Clean up package lists to reduce image size
    # sodium required for lexik auth bundle
    # amqp and librabbitmq-dev required for rabbitMQ

# Set working directory
WORKDIR /var/www/html/users_api

# Copy application files
COPY . /var/www/html/users_api/

# Set entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint"]
