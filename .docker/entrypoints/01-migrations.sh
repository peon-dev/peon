#!/usr/bin/env bash

# Wait for database to be ready

# TODO: I dont like this to be hardcoded, it should parse DATABASE_URL env var instead and use host+port part
wait-for-it mariadb:3306 --timeout=60

# Run doctrine migrations
bin/console doctrine:migrations:migrate --no-interaction
