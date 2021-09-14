# PHP Mate

## Development

For development, clone repository and run it via `docker-compose up`

To get into Docker container (etc for development or debugging):

```shell
docker-compose run --rm php bash
```

### Testing

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
        environment:
            FORWARD_PORT: 8080
            # you can use https://hostingcanada.org/htpasswd-generator/ 
            # note $$ (it is escaped $ char)
            # default credentials: phpmate:phpmate
            HTPASSWD: phpmate:$$apr1$$crgzhdtf$$ZOr9u1GXhfUyT7pBcUuZ51
    
    dashboard:
        image: ghcr.io/phpmate/phpmate:master
        environment:
            DATABASE_URL: "mysql://root:root@mariadb:3306/phpmate?serverVersion=mariadb-10.6.4&charset=utf8"
        restart: unless-stopped
        command: [ "php", "-S", "0.0.0.0:8080", "-t", "dashboard/public" ]
        # If not using auth proxy, you need to make this service available:
        # ports:
        #     - 8080:8080

    worker:
        image: ghcr.io/phpmate/phpmate:master
        environment:
            DATABASE_URL: "mysql://root:root@mariadb:3306/phpmate?serverVersion=mariadb-10.6.4&charset=utf8"
        restart: unless-stopped
        command: [ "bin/worker" ]

    scheduler:
        image: ghcr.io/phpmate/phpmate:master
        environment:
            DATABASE_URL: "mysql://root:root@mariadb:3306/phpmate?serverVersion=mariadb-10.6.4&charset=utf8"
        restart: unless-stopped
        command: [ "bin/scheduler" ]

    mariadb:
        image: mariadb:10.6.4
        restart: unless-stopped
        volumes:
            - ./db-data:/var/lib/mysql
        environment:
            MYSQL_DATABASE: phpmate
            MYSQL_ROOT_PASSWORD: root
```

Then run `docker-compose up`

It is recommended to set up daily cron that will pull newer Docker images:
```
0 0 * * *    docker-compose -f /path/to/docker-compose.yml pull
```
