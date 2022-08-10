#!/bin/bash

set -e

role=${CONTAINER_ROLE:-main}
env=${APP_ENV:-production}

# cache configuration
if [ "$env" == "production" ]; then
  echo "Caching configuration..."
  (cd /var/www/html && php artisan config:cache && php artisan route:cache && php artisan view:cache)
fi

# run by role
if [ "$role" = "main" ]; then

  echo "do migration ..."
  php migrate --force

  echo "start main service ..."
  exec apache2-foreground

elif [ "$role" = "queue" ]; then

  echo "start queue service ..."
  php /var/www/html/artisan queue:work --tries=3 --timeout=180

elif [ "$role" = "scheduler" ]; then

  echo "start scheduler service ..."
  while [ true ]; do
    php /var/www/html/artisan schedule:run >>/dev/null 2>&1 &
    sleep 60
  done

else
  echo "Could not match the container role \"$role\""
  exit 1
fi
