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

Xdebug extension is installed and enabled. If you need to overwrite defaults, please set `PEON_XDEBUG_CONFIG` which gets propagated into `XDEBUG_CONFIG` variable. Read more in the [xdebug documentation](https://xdebug.org/docs/all_settings#XDEBUG_CONFIG):
```
export SHARRY_XDEBUG_CONFIG="client_host=localhost log=/tmp/xdebug.log"
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
            MERCURE_PUBLIC_URL: "http://localhost:8081/.well-known/mercure"
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
            - "8081:80"
```

Then run `docker-compose up`

It is recommended to set up daily cron that will pull newer Docker images:
```
0 0 * * *    docker-compose -f /path/to/docker-compose.yml pull
```
It is good idea to restart containers after pulling new image as well.

## Troubleshooting

#### Process failing with docker permission error:
```
docker: Got permission denied while trying to connect to the Docker daemon socket at unix:///var/run/docker.sock: Post "http://%2Fvar%2Frun%2Fdocker.sock/v1.24/containers/create": dial unix /var/run/docker.sock: connect: permission denied.
See 'docker run --help'.
```

#### Possible fix:

Check out who owns the Docker socket, for example by running `ls -l /var/run/docker.sock` and set the group id to the `worker` process:

Overwrite `worker` service group/user in `docker-compose.override.yml`: 
```yaml
version: "3.7"
services:
    worker:
        user: "peon:<docker group id or name>"
        
        # in safe environment, you can use root user
        # be aware of it can mess with ownership of the peon source code (for example cache files owned by root)
        user: "root"
```
