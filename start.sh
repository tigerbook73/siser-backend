#!/bin/bash

set -e

role=${CONTAINER_ROLE:-main}
env=${APP_ENV:-production}

# cache configuration
if [ "$env" == "production" ]; then
  echo "Caching configuration..."
  runuser -u www-data -- php artisan config:cache &&
  runuser -u www-data -- php artisan route:cache &&
  runuser -u www-data -- php artisan view:cache
fi

# run by role
if [ "$role" = "main" ] || [ "$role" = "customer" ] || [ "$role" = "admin" ]; then

  echo "do migration ..."
  runuser -u www-data -- php artisan migrate --force

  echo "start $role service ..."
  exec apache2-foreground

elif [ "$role" = "queue" ]; then

  echo "start queue service ..."
  while [ true ]; do
      echo "run queue worker ..."
      runuser -u www-data -- php /var/www/html/artisan queue:work --tries=3 --timeout=180 --max-jobs=1000
  done

elif [ "$role" = "scheduler" ]; then

  echo "start scheduler service ..."
  while [ true ]; do
    runuser -u www-data -- php /var/www/html/artisan schedule:run >>/dev/null 2>&1 &
    sleep 60
  done

else
  echo "Could not match the container role \"$role\""
  exit 1
fi
