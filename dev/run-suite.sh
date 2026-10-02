#!/bin/sh
set -u

php -d pcov.enabled=1 -d pcov.directory=/app /usr/local/bin/phpunit \
    --configuration dev/phpunit.xml \
    --log-junit /coverage/junit.xml \
    --coverage-text \
    --coverage-clover /coverage/clover.xml \
    --coverage-html /coverage/html
result=$?
php dev/summarize-coverage.php
summary_result=$?
if [ "$result" -ne 0 ]; then
    exit "$result"
fi
exit "$summary_result"
