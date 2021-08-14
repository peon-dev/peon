# PHPMate

## Usage

To get into Docker container (etc for development or debugging):

```shell
docker-compose run --rm php bash
```

### Rector on GitLab repository

This will run Rector on your GitLab repository and if there are any changes, open MR for you.

Repository URI **MUST** be https clone url of your repository *(example: https://gitlab.com/phpmate-dogfood/rector.git)*.

```shell
docker-compose run php bin/run-rector-on-gitlab.php <repositoryUri> <username> <personalAccessToken>
```

## Testing

In order to run end-to-end tests, you need to create `.env.test.local` and provide variable values there (see `.env.test` for list of variables).

## Xdebug

To run with xdebug create `docker-compose.override.yml` and configure environment in:
```yaml
version: "3.7"
services:
    php:
        environment:
            XDEBUG_CONFIG: "client_host=192.168.64.1"
            PHP_IDE_CONFIG: "serverName=phpmate"
```
