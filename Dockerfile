FROM php:8.0-cli as dev

ENV COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Very convenient PHP extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN mkdir /.composer \
    && chown 1000:1000 /.composer

RUN apt-get update && apt-get install -y \
    git \
    zip

RUN install-php-extensions \
    intl \
    zip \
    pdo_pgsql \
    xdebug

COPY .docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY .docker/wait-for-it.sh /usr/local/bin/wait-for-it
RUN chmod +x /usr/local/bin/wait-for-it

COPY .docker/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

RUN mkdir /docker-entrypoint.d/
COPY .docker/entrypoints/*.sh /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*.sh

USER 1000:1000



FROM node:14 as js-builder

WORKDIR /build

# Install npm packages
COPY package.json yarn.lock webpack.config.js ./
RUN yarn install

# Production yarn build
COPY ./assets ./assets

RUN yarn run build



FROM dev as prod

USER root

# Unload xdebug extension by deleting config
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir -p /www && chown 1000:1000 /www

USER 1000:1000
WORKDIR "/www"

COPY --chown=1000:1000 --from=js-builder . .
COPY --chown=1000:1000 . .

RUN composer install --no-interaction
