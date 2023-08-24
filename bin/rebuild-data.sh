#!/bin/bash

set -e
set -x

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

php $BASEDIR/../artisan migrate:fresh

if [ "$1" == "--model" ]; then
  php $BASEDIR/../artisan code:models
fi

php $BASEDIR/../artisan db:seed
php $BASEDIR/../artisan dr:cmd clear
php $BASEDIR/../artisan dr:cmd init
php $BASEDIR/../artisan dr:cmd enable-hook

rm -rf $BASEDIR/../storage/logs/laravel.log

