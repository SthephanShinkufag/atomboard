#!/bin/sh
set -u

php -d pcov.enabled=1 -d pcov.directory=/app /usr/local/bin/phpunit \
    --configuration dev/phpunit.xml \
    --filter 'DatabaseTest|FunctionsTest|RenderingTest' \
    --log-junit /coverage/junit.xml \
    --coverage-text \
    --coverage-clover /coverage/clover.xml \
    --coverage-html /coverage/html
