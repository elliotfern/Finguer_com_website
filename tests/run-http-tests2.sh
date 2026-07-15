#!/bin/bash
# tests/run-http-tests2.sh

php -S 127.0.0.1:8000 tests/router-test.php &
SERVER_PID=$!

sleep 1

phpunit --group http --debug "$@"
RESULT=$?

kill $SERVER_PID

exit $RESULT
