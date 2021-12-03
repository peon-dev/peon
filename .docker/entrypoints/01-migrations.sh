#!/usr/bin/env bash

# Referenced and tweaked from http://stackoverflow.com/questions/6174220/parse-url-in-shell-script#6174447
function extract_host_from_dsn
{
    dsn=$1

    proto="$(echo $dsn | grep :// | sed -e's,^\(.*://\).*,\1,g')"
    # remove the protocol
    url="$(echo ${dsn/$proto/})"
    # extract the user (if any)
    userpass="$(echo $url | grep @ | cut -d@ -f1)"
    pass="$(echo $userpass | grep : | cut -d: -f2)"
    if [ -n "$pass" ]; then
    user="$(echo $userpass | grep : | cut -d: -f1)"
    else
      user=$userpass
    fi

    # extract the host+port
    host="$(echo ${url/$user:$pass@/} | cut -d/ -f1)"

    echo $host
}

if [ -n "$DATABASE_URL" ]; then
    db_host=$(extract_host_from_dsn $DATABASE_URL)
else
    db_host=postgres:5432
fi

# Wait for db connection to be ready
wait-for-it $db_host --timeout=60

# To make sure, wait few more seconds for database system to start up (useful for cold starts)
sleep 7

# Run doctrine migrations
bin/console doctrine:migrations:migrate --no-interaction
