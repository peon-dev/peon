# PHP Mate

## Development

For development, clone repository and run it via `docker-compose up`

To get into Docker container (etc for development or debugging):

```shell
docker-compose run --rm dashboard bash
```

### Testing

Tests can be run via `vendor/bin/phpunit` (in PHP container).

In order to run end-to-end tests, you need to create `.env.test.local` and provide variable values there (see `.env.test` for list of variables).

### Xdebug

To run with xdebug create `docker-compose.override.yml` and configure environment in:
```yaml
version: "3.7"
services:
    php:
        environment:
            XDEBUG_CONFIG: "client_host=192.168.64.1"
            PHP_IDE_CONFIG: "serverName=phpmate"
```


## Production use

PHP Mate is available as Docker image: `ghcr.io/phpmate/phpmate`

Inspiration for `docker-compose.yml`:

```yaml
version: "3.7"
services:
    # Optional to protect with basic http auth
    nginx-auth-proxy:
        image: beevelop/nginx-basic-auth:latest
        ports:
            - 8080:80
        depends_on:
            - dashboard
        environment:
            FORWARD_PORT: 8080
            # you can use https://hostingcanada.org/htpasswd-generator/ 
            # note $$ (it is escaped $ char)
            # default credentials: phpmate:phpmate
            HTPASSWD: phpmate:$$apr1$$crgzhdtf$$ZOr9u1GXhfUyT7pBcUuZ51

    dashboard:
        image: ghcr.io/phpmate/phpmate:master
        environment:
            DATABASE_URL: "postgresql://phpmate:phpmate@postgres:5432/phpmate?serverVersion=13&charset=utf8"
        restart: unless-stopped
        depends_on:
            - postgres
        entrypoint: [ "bash", "/docker-entrypoint.sh" ]
        command: [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]
        # If not using auth proxy, you need to make this service available:
        # ports:
        #     - 8080:8080

    worker:
        image: ghcr.io/phpmate/phpmate:master
        depends_on:
            - dashboard
        environment:
            DATABASE_URL: "postgresql://phpmate:phpmate@postgres:5432/phpmate?serverVersion=13&charset=utf8"
        restart: unless-stopped
        command: [ "wait-for-it", "dashboard:8080", "--", "bin/worker" ]

    scheduler:
        image: ghcr.io/phpmate/phpmate:master
        depends_on:
            - dashboard
        environment:
            DATABASE_URL: "postgresql://phpmate:phpmate@postgres:5432/phpmate?serverVersion=13&charset=utf8"
        restart: unless-stopped
        command: [ "wait-for-it", "dashboard:8080", "--", "bin/scheduler" ]

    postgres:
        image: postgres:13
        environment:
            POSTGRES_USER: phpmate
            POSTGRES_PASSWORD: phpmate
        volumes:
            - ./db-data:/var/lib/postgresql/data
```

Then run `docker-compose up`

It is recommended to set up daily cron that will pull newer Docker images:
```
0 0 * * *    docker-compose -f /path/to/docker-compose.yml pull
```
It is good idea to restart containers after pulling new image as well.
