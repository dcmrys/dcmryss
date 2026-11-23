#!/bin/sh
set -eu

port="${PORT:-10000}"
case "$port" in
    ''|*[!0-9]*)
        echo "PORT must contain only digits." >&2
        exit 1
        ;;
esac

sed -i "s/^Listen .*/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_DB_SEEDER:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache

exec "$@"
