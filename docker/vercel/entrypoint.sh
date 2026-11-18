#!/bin/sh
set -eu

php artisan config:clear
php artisan route:clear
php artisan view:clear

exec "$@"
