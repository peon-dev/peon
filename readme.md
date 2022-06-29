# Peon

## Development

For development, clone repository and run it via `docker-compose up`

When running for the first time, user will be automatically created for you, with username and password `peon`. 

To get into Docker container (etc for development or debugging):

```shell
docker-compose run --rm dashboard bash
```

### Testing

`vendor/bin/phpunit` (in PHP container)  
or <small>`docker-compose run --rm dashboard vendor/bin/phpunit` outside of container.</small>

To run application tests, webpack must be built: `yarn install && yarn run dev`  
*If you are using Docker for development, this is take care of already by `js-watch` service*. 

In order to run end-to-end tests, you need to create `.env.test.local` and provide variable values there (see `.env.test` for list of variables).

### Xdebug

To run with xdebug create `docker-compose.override.yml` and configure environment in:
```yaml
version: "3.7"
services:
    php:
        environment:
            XDEBUG_CONFIG: "client_host=192.168.64.1"
            PHP_IDE_CONFIG: "serverName=peon"
```


## Production use

Peon is available as Docker image: `ghcr.io/peon-dev/peon`

Inspiration for `docker-compose.yml`:

```yaml
version: "3.7"

volumes:
    unit-state:
    postgres-data:
    caddy_data:
    caddy_config:

# Reusable variables for php services to not repeat yourself
# Make sure that credentials in DATABASE_URL matches credentials in postgres service
x-php-common-variables: &php-common-variables
    DATABASE_URL: "postgresql://peon:peon@postgres:5432/peon?serverVersion=13&charset=utf8"
    MERCURE_JWT_SECRET: '!ChangeMe!'

services:
    # Helper service to run database migrations
    db-migrations:
        image: ghcr.io/peon-dev/peon:main
        environment:
            <<: *php-common-variables
        depends_on:
            - postgres
        command: "bash -c 'wait-for-it postgres:5432 -- sleep 3 && bin/console doctrine:migrations:migrate --no-interaction'"

    dashboard:
        image: ghcr.io/peon-dev/peon:main
        environment:
            <<: *php-common-variables
            # Change to match your host:
            MERCURE_PUBLIC_URL: "http://localhost:8180/.well-known/mercure"
        volumes:
          - unit-state:/var/lib/unit/state
        restart: unless-stopped
        depends_on:
            - db-migrations
            - postgres
            - mercure
        ports:
            - "8080:8080"

    worker:
        image: ghcr.io/peon-dev/peon:main
        depends_on:
            - db-migrations
        volumes:
            - /var/run/docker.sock:/var/run/docker.sock
            - $PWD/working_directories:/peon/var/working_directories
        environment:
            <<: *php-common-variables
            HOST_WORKING_DIRECTORIES_PATH: $PWD/working_directories
        restart: unless-stopped
        command: "bash -c 'wait-for-it postgres:5432 -- sleep 6 && bin/worker'"

    scheduler:
        image: ghcr.io/peon-dev/peon:main
        depends_on:
            - db-migrations
        environment:
            <<: *php-common-variables
        restart: unless-stopped
        command: "bash -c 'wait-for-it postgres:5432 -- sleep 6 && bin/scheduler'"

    postgres:
        image: postgres:13
        environment:
            POSTGRES_USER: peon
            POSTGRES_PASSWORD: peon
            POSTGRES_DB: peon
        volumes:
            - postgres-data:/var/lib/postgresql/data

    mercure:
        image: dunglas/mercure
        restart: unless-stopped
        environment:
            SERVER_NAME: ':80'
            MERCURE_PUBLISHER_JWT_KEY: '!ChangeMe!'
            MERCURE_SUBSCRIBER_JWT_KEY: '!ChangeMe!'
            # Set the URL of your instance (without trailing slash!) as value of the cors_origins directive
            MERCURE_EXTRA_DIRECTIVES: |
                cors_origins *
        volumes:
            - caddy_data:/data
            - caddy_config:/config
        ports:
            - "8180:80"
```

Then run `docker-compose up`

It is recommended to set up daily cron that will pull newer Docker images:
```
0 0 * * *    docker-compose -f /path/to/docker-compose.yml pull
```
It is good idea to restart containers after pulling new image as well.
