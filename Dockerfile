FROM ghcr.io/peon-dev/php:main as composer

ENV APP_ENV="prod"
ENV APP_DEBUG=0

USER root

# Unload xdebug extension by deleting config
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir -p /peon/var/cache && chown -R peon /peon

USER peon
WORKDIR /peon

# Intentionally split into multiple steps to leverage docker layer caching
COPY --chown=peon composer.json composer.lock symfony.lock ./

RUN composer install --no-interaction --no-scripts



FROM node:14 as js-builder

WORKDIR /build

# We need /vendor here
COPY --from=composer /peon .

# Install npm packages
COPY package.json yarn.lock webpack.config.js ./
RUN yarn install

# Production yarn build
COPY ./assets ./assets

RUN yarn run build



FROM composer as prod

COPY --chown=peon .docker/entrypoints/*.sh /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*.sh

# Copy js build
COPY --chown=peon --from=js-builder /build .

# Copy application source code
COPY --chown=peon . .

# Need to run again to trigger scripts with application code present
RUN composer install --no-interaction
