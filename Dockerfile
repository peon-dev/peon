FROM php:8.0-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1 COMPOSER_NO_INTERACTION=1

RUN apt-get update && apt-get install -y \
        libzip4 \
        libicu63 \
        g++ \
        git \
        libicu-dev \
        libzip-dev \
        unzip \
        wget \
        zip \
    && pecl -q \
        install \
        zip \
        xdebug \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        zip

COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
