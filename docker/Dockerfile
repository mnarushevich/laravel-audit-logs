FROM php:8.4-fpm-alpine

# Set environment variables
ENV XDEBUG_MODE=develop,debug \
    XDEBUG_CONFIG="client_host=host.docker.internal" \
    PHP_IDE_CONFIG="serverName=Docker"

# Install required dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    icu-dev \
    oniguruma-dev \
    shadow \
    && docker-php-ext-install \
    zip \
    pdo_mysql \
    intl \
    mbstring \
    exif \
    bcmath \
    pcntl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Expose port for Xdebug
EXPOSE 9003

# Command to keep the container running
CMD ["php-fpm", "-F"]