FROM php:8.0-cli as dev

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

RUN mkdir /.composer \
    && chown 1000:1000 /.composer

USER 1000

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_MEMORY_LIMIT=-1 COMPOSER_NO_INTERACTION=1

COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

FROM dev as prod

# Unload xdebug extension by deleting config
RUN rm /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR "/www"

# To elevate Docker cache we try first composer files - maybe packages did not change
COPY composer.json ./composer.json
COPY composer.lock ./composer.lock

RUN composer install

# Now the rest of source code
COPY . .
