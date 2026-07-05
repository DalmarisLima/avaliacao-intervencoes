#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deploy caches gerados com sucesso."
