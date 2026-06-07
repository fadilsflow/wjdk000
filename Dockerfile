# syntax=docker/dockerfile:1.7

FROM node:22-bookworm-slim AS node-base

FROM node-base AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM node-base AS whatsapp-gateway-deps
ENV PUPPETEER_SKIP_DOWNLOAD=true \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
WORKDIR /gateway
COPY whatsapp-gateway/package*.json ./
RUN npm ci --omit=dev

FROM composer:2 AS vendor
WORKDIR /app
COPY . ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --optimize-autoloader

FROM php:8.4-apache-bookworm AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    CACHE_STORE=file \
    SESSION_DRIVER=file \
    QUEUE_CONNECTION=database \
    GATEWAY_PORT=3000 \
    WHATSAPP_AUTH_DATA_PATH=/var/www/html/storage/app/whatsapp-auth \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
    RUN_QUEUE_WORKER=true \
    RUN_SCHEDULER=true \
    RUN_SEEDERS_ONCE=false \
    RUN_OPTIMIZE=true

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        chromium \
        curl \
        fonts-liberation \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        supervisor \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=node-base /usr/local/bin/node /usr/local/bin/node
COPY --from=vendor --chown=www-data:www-data /app ./
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
COPY --from=whatsapp-gateway-deps --chown=www-data:www-data /gateway/node_modules ./whatsapp-gateway/node_modules
COPY --chown=www-data:www-data whatsapp-gateway/server.js ./whatsapp-gateway/server.js
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint \
    && mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/app/whatsapp-auth \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache database whatsapp-gateway

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up >/dev/null || exit 1

ENTRYPOINT ["docker-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
