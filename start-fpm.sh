#!/bin/bash

set -e

role=${CONTAINER_ROLE:-main}
env=${APP_ENV:-production}

# cache configuration
if [ "$env" == "production" ] || [ "$env" == "staging" ]; then
  echo "Caching configuration..."
  php artisan config:cache &&
  php artisan route:cache &&
  php artisan view:cache
fi

# run by role
echo "do migration ..."
php artisan migrate --force

# update webhook
php artisan dr:cmd enable-hook

echo "start $role service ..."
php artisan octane:roadrunner --log-level=debug
