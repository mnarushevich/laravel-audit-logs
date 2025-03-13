FROM php:8.4-fpm-alpine

ENV XDEBUG_MODE=develop,debug \
    XDEBUG_CONFIG="client_host=host.docker.internal" \
    PHP_IDE_CONFIG="serverName=Docker"

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

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9003

CMD ["php-fpm", "-F"]