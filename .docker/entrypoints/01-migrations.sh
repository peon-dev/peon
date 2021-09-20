#!/usr/bin/env bash

# Wait for database to be ready
wait-for-it db:3306 --timeout=60

# Run doctrine migrations
bin/console doctrine:migrations:migrate --no-interaction
