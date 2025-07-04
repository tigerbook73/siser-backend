#!/bin/bash

set -e
# set -x

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

cd $BASEDIR/../

version=${1:-9.9.9}

DEV_VERSION=$version docker compose down --volumes --remove-orphans