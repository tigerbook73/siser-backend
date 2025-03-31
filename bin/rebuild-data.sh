#!/bin/bash

set -e
# set -x

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

# only run in local environment
APP_ENV=$(php $BASEDIR/../artisan env | sed -n 's/.*\[\(.*\)\].*/\1/p')
if [ "$APP_ENV" != "local" ]; then
  echo "Script stopped because APP_ENV is not local"
  exit 1
fi

echo ""
echo "------------------- rebuild database ..."
php $BASEDIR/../artisan migrate:fresh
echo "------------------- rebuild database done!"

if [ "$1" == "--model" ]; then
  echo ""
  echo "------------------- re-generate models ..."
  php $BASEDIR/../artisan code:models
  echo "------------------- re-generate models done!"
fi

echo ""
echo "------------------- re-seed database ..."
php $BASEDIR/../artisan db:seed
echo "------------------- re-seed database done!"

echo ""
echo "------------------- synchronize products information to paddle ..."
php $BASEDIR/../artisan paddle:cmd sync-all
echo "------------------- synchronize products information to paddle done!"

echo ""
echo "------------------- stop all subscriptions in paddle ..."
php $BASEDIR/../artisan paddle:cmd stop-all-subscriptions
echo "------------------- stop all subscriptions in paddle done!"

echo ""
echo "------------------- remove laravel log ..."
rm -rf $BASEDIR/../storage/logs/laravel.log
echo "------------------- remove laravel log done!"


if [ "$1" == "--model" ]; then
  echo ""
  echo "------------------- rebuild database and seed for test database ..."
  DB_DATABASE=testing php $BASEDIR/../artisan migrate:fresh --seed
  echo "------------------- rebuild database and seed for test database done!"
fi
