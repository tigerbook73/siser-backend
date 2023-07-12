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
echo "do migration ..."
runuser -u www-data -- php artisan migrate --force

echo "start $role service ..."
exec php-fpm
