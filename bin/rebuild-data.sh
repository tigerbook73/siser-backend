#!/bin/bash

set -e
set -x

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

php $BASEDIR/../artisan migrate:fresh
php $BASEDIR/../artisan db:seed
php $BASEDIR/../artisan dr:cmd clear
php $BASEDIR/../artisan dr:cmd init

