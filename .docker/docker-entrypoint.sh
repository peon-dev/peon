#!/bin/sh

# This is original entrypoint, decorated with running scripts in directory
# See: https://github.com/docker-library/php/blob/master/docker-php-entrypoint

set -e

# custom "decoration" - run scripts in /docker-entrypoint.d/ directory
if /usr/bin/find "/docker-entrypoint.d/" -mindepth 1 -print -quit 2>/dev/null | /bin/grep -q .; then
    for f in $(/usr/bin/find /docker-entrypoint.d/ -type f -name "*.sh"); do
        echo "$0: Launching $f";
        "$f"
    done
fi

# continue with original entrypoint

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

exec "$@"
