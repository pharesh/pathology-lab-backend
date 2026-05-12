#!/usr/bin/env sh
set -e

cd /app

mkdir -p \
  bootstrap/cache \
  storage/app \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/testing \
  storage/logs

chmod -R 775 bootstrap/cache storage
chown -R www-data:www-data bootstrap/cache storage || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
