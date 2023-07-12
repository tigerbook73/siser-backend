#!/bin/bash

set -e

role=${CONTAINER_ROLE:-queue}
env=${APP_ENV:-production}

# cache configuration
if [ "$env" == "production" ]; then
  echo "Caching configuration..."
  runuser -u www-data -- php artisan config:cache
fi

echo "do migration ..."
runuser -u www-data -- php artisan migrate --force

(
  echo "start queue service ..."
  while [ true ]; do
    echo "queue worker starts ..."
    runuser -u www-data -- php /var/www/html/artisan queue:work --tries=3 --timeout=180 --max-jobs=1000
    echo "queue worker exits"
  done
)&
taskQueue=$!

(
  echo "start schedule service ..."
  while [ true ]; do
    runuser -u www-data -- php /var/www/html/artisan schedule:run &
    sleep 60
  done
)&
taskScheduler=$!

wait -n
kill $(jobs -p)
