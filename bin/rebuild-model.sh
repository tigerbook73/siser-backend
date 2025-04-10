#!/bin/bash

BASEDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"

$BASEDIR/rebuild-data.sh --model
