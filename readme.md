# Rector bot

## Usage

To get into Docker container:

```shell
docker-compose run --rm php bash
```

In container:

```shell
php bin/gitlab-repository.php <repository/name>
```

## Xdebug

To run with xdebug create `docker-compose.override.yml` and configure environment in:
```yaml
version: "3.7"
services:
    php:
        environment:
            XDEBUG_CONFIG: "client_host=192.168.64.1"
            PHP_IDE_CONFIG: "serverName=rectorbot"
```
