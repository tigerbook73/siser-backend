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

php $BASEDIR/../artisan migrate:fresh

if [ "$1" == "--model" ]; then
  php $BASEDIR/../artisan code:models
fi

php $BASEDIR/../artisan db:seed
# php $BASEDIR/../artisan dr:cmd clear
# php $BASEDIR/../artisan dr:cmd init
# php $BASEDIR/../artisan dr:cmd enable-hook

php $BASEDIR/../artisan paddle:cmd sync-all

rm -rf $BASEDIR/../storage/logs/laravel.log

