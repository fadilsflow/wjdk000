#!/usr/bin/env sh
set -eu

cd /var/www/html

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export LOG_LEVEL="${LOG_LEVEL:-info}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"
export CACHE_STORE="${CACHE_STORE:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export GATEWAY_PORT="${GATEWAY_PORT:-3000}"
export WHATSAPP_GATEWAY_URL="${WHATSAPP_GATEWAY_URL:-http://127.0.0.1:${GATEWAY_PORT}/send}"
export WHATSAPP_AUTH_DATA_PATH="${WHATSAPP_AUTH_DATA_PATH:-/var/www/html/storage/app/whatsapp-auth}"
export PUPPETEER_EXECUTABLE_PATH="${PUPPETEER_EXECUTABLE_PATH:-/usr/bin/chromium}"
export HOME="${APP_HOME:-/var/www/html/storage/app}"
export XDG_CONFIG_HOME="${XDG_CONFIG_HOME:-/var/www/html/storage/app/.config}"
export PUPPETEER_CACHE_DIR="${PUPPETEER_CACHE_DIR:-/var/www/html/storage/app/.cache/puppeteer}"

if [ -z "${GATEWAY_SECRET_TOKEN:-}" ] && [ -n "${WHATSAPP_GATEWAY_TOKEN:-}" ]; then
    export GATEWAY_SECRET_TOKEN="${WHATSAPP_GATEWAY_TOKEN}"
fi

if [ -z "${WHATSAPP_GATEWAY_TOKEN:-}" ] && [ -n "${GATEWAY_SECRET_TOKEN:-}" ]; then
    export WHATSAPP_GATEWAY_TOKEN="${GATEWAY_SECRET_TOKEN}"
fi

if [ -z "${GATEWAY_SECRET_TOKEN:-}" ]; then
    generated_token="$(php -r 'echo bin2hex(random_bytes(32));')"
    export GATEWAY_SECRET_TOKEN="${generated_token}"
    export WHATSAPP_GATEWAY_TOKEN="${generated_token}"
    echo "[entrypoint] WHATSAPP_GATEWAY_TOKEN/GATEWAY_SECRET_TOKEN kosong; memakai token runtime sementara. Set env ini agar stabil."
fi

if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show --no-interaction)"
    echo "[entrypoint] APP_KEY kosong; memakai key runtime sementara. Set APP_KEY permanen untuk production."
fi

mkdir -p \
    database \
    storage/app/public \
    storage/app/private \
    storage/app/whatsapp-auth \
    storage/app/.config \
    storage/app/.cache/puppeteer \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "${DB_CONNECTION:-}" = "sqlite" ] && [ -n "${DB_DATABASE:-}" ]; then
    mkdir -p "$(dirname "${DB_DATABASE}")"
    touch "${DB_DATABASE}"
fi

chown -R www-data:www-data storage bootstrap/cache database whatsapp-gateway 2>/dev/null || true

php artisan optimize:clear --no-interaction || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    attempts="${MIGRATION_RETRIES:-30}"
    attempt=1

    until php artisan migrate --force --no-interaction; do
        if [ "${attempt}" -ge "${attempts}" ]; then
            echo "[entrypoint] Migrasi gagal setelah ${attempts} percobaan."
            exit 1
        fi

        attempt=$((attempt + 1))
        echo "[entrypoint] Migrasi belum berhasil; coba lagi (${attempt}/${attempts}) dalam 2 detik..."
        sleep 2
    done
fi

if [ "${RUN_SEEDERS_ONCE:-false}" = "true" ]; then
    seed_marker="storage/app/.seeded"

    if [ ! -f "${seed_marker}" ]; then
        php artisan db:seed --force --no-interaction
        touch "${seed_marker}"
        chown www-data:www-data "${seed_marker}" 2>/dev/null || true
    fi
elif [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

if [ ! -e public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

if [ "${RUN_OPTIMIZE:-true}" = "true" ]; then
    php artisan optimize --no-interaction
fi

exec "$@"
