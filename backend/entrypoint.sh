#!/bin/sh
sleep 5
php /app/migrations.php

while true; do
    find /app/public /app/src -name '*.php' | entr -n -r php -S 0.0.0.0:8080 -t /app/public
done
