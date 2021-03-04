FROM php:8.0-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1 COMPOSER_NO_INTERACTION=1

RUN pecl install xdebug

COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
