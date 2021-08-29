FROM php:8.0-cli as dev

ENV COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN mkdir /.composer \
    && chown 1000:1000 /.composer \

RUN install-php-extensions \
    intl \
    zip \
    pdo_mysql \
    xdebug

COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

USER 1000:1000


FROM dev as prod

USER root

# Unload xdebug extension by deleting config
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir -p /www && chown 1000:1000 /www

USER 1000:1000
WORKDIR "/www"

COPY --chown=1000:1000 . .

RUN composer install --no-interaction
