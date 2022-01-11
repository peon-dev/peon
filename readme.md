# Peon

## Development

For development, clone repository and run it via `docker-compose up`

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
services:
    dashboard:
        image: ghcr.io/peon-dev/peon:master
        environment:
            DATABASE_URL: "postgresql://peon:peon@postgres:5432/peon?serverVersion=13&charset=utf8"
            # Change to match your host:
            MERCURE_PUBLIC_URL: "http://localhost:8081/.well-known/mercure"
            MERCURE_JWT_SECRET: '!ChangeMe!'
        restart: unless-stopped
        depends_on:
            - postgres
            - mercure
        entrypoint: [ "bash", "/docker-entrypoint.sh" ]
        command: [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]
        ports:
            - 8080:8080

    worker:
        image: ghcr.io/peon-dev/peon:master
        depends_on:
            - dashboard
            - mercure
        environment:
            DATABASE_URL: "postgresql://peon:peon@postgres:5432/peon?serverVersion=13&charset=utf8"
            MERCURE_JWT_SECRET: '!ChangeMe!'
        restart: unless-stopped
        command: [ "wait-for-it", "dashboard:8080", "--", "bin/worker" ]

    scheduler:
        image: ghcr.io/peon-dev/peon:master
        depends_on:
            - dashboard
            - mercure
        environment:
            DATABASE_URL: "postgresql://peon:peon@postgres:5432/peon?serverVersion=13&charset=utf8"
            MERCURE_JWT_SECRET: '!ChangeMe!'
        restart: unless-stopped
        command: [ "wait-for-it", "dashboard:8080", "--", "bin/scheduler" ]

    postgres:
        image: postgres:13
        environment:
            POSTGRES_USER: peon
            POSTGRES_PASSWORD: peon
        volumes:
            - ./db-data:/var/lib/postgresql/data

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
            - ./mercure-data/data:/data
            - ./mercure-data/config:/config
        ports:
            - 8081:80
```

Then run `docker-compose up`

It is recommended to set up daily cron that will pull newer Docker images:
```
0 0 * * *    docker-compose -f /path/to/docker-compose.yml pull
```
It is good idea to restart containers after pulling new image as well.
