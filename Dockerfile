FROM php:8.0-cli as dev

RUN apt-get update && apt-get install -y \
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

RUN mkdir /.composer \
    && chown 1000:1000 /.composer

USER 1000:1000

ENV COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


FROM dev as prod

USER root

# Unload xdebug extension by deleting config
RUN rm /usr/local/etc/php/conf.d/xdebug.ini

RUN mkdir -p /www && chown 1000:1000 /www

USER 1000:1000
WORKDIR "/www"

COPY --chown=1000:1000 . .

RUN composer install --no-interaction
