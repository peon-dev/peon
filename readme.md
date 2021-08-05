# PHP Mate

## Development

For development, clone repository and run it via `docker-compose up`


## Production use

PHP Mate is available as Docker image: `ghcr.io/phpmate/phpmate`

Inspiration for `docker-compose.yml`:

```yaml
version: "3.7"
services:
    dashboard:
        image: ghcr.io/phpmate/phpmate:master
        environment:
            BASIC_AUTH_USER: "phpmate"
            BASIC_AUTH_PASSWORD: "phpmate"
        ports:
            - 8080:8080
        command: [ "php", "-S", "0.0.0.0:8080", "-t", "dashboard/public" ]
        restart: unless-stopped

    worker:
        image: ghcr.io/phpmate/phpmate:master
        command: [ "worker/bin/worker" ]
        restart: unless-stopped

    scheduler:
        image: ghcr.io/phpmate/phpmate:master
        command: [ "scheduler/bin/scheduler" ]
        restart: unless-stopped
```

Then run `docker-compose --project-name=phpmate up`

It is recommended to set up daily cron that will pull newer Docker images:
```
0 0 * * *    cd /path/to/your-docker-compose && docker-compose pull
```
