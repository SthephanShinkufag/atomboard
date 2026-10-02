#!/bin/sh
set -eu

runuser -u www-data -- php dev/configure.php
if [ "${SEED_TEST_DATA:-1}" != "0" ]; then
    runuser -u www-data -- php dev/seed.php
fi
if [ -d /web-coverage ]; then
    chown www-data:www-data /web-coverage
fi
exec docker-php-entrypoint "$@"
