#!/bin/bash

set -e
set -x

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

version=${1:-9.9.9}

# build frontend
script="$BASEDIR/../../siser-frontend/bin/build-local.sh"
echo $script
if [ -f "$script" ] && [ -x "$script" ]; then
  echo build frontend 
  $script $version
fi

# build frontend-admin
script="$BASEDIR/../../siser-frontend-admin/bin/build-local.sh"
echo $script
if [ -f "$script" ] && [ -x "$script" ]; then
  echo build frontend admin
  $script $version
fi

cd $BASEDIR/../
DEV_VERSION=$version docker compose build
