#!/usr/bin/env bash

set -Eeuo pipefail

echo "Ping site"

curl -fsS https://syncstats.com/index.html >/dev/null

echo "Ping admin"

curl -fsS https://syncstats.com/admin >/dev/null

echo "Ping api"

curl -fsS https://syncstats.com/api >/dev/null

echo "OK"
