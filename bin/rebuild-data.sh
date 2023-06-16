#!/bin/bash

set -e
set -x

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

php $BASEDIR/../artisan migrate:fresh

if [ "$1" == "--model" ]; then
  php $BASEDIR/../artisan code:models

  # TODO: temporary: remove extra lines
  # sed -i '/LdsPool\|LdsRegistration/d' $BASEDIR/../app/Models/Base/User.php
  # sed -i '/lds_pool\|lds_registrations/,+3d' $BASEDIR/../app/Models/Base/User.php
fi

php $BASEDIR/../artisan db:seed
php $BASEDIR/../artisan dr:cmd clear
php $BASEDIR/../artisan dr:cmd init

rm -rf $BASEDIR/../storage/logs/laravel.log

