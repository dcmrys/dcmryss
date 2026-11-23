FROM php:8.3-apache-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev libpq-dev libxml2-dev \
    && docker-php-ext-install curl dom mbstring opcache pdo_pgsql xml \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY --from=vendor /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN mkdir -p storage/framework/views && npm run build

FROM php-base AS app

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    APACHE_DOCUMENT_ROOT=/var/www/html/public

COPY --from=vendor /var/www/html /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/render-entrypoint.sh /usr/local/bin/render-entrypoint

RUN a2enmod headers rewrite \
    && chmod +x /usr/local/bin/render-entrypoint \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/render-entrypoint"]
CMD ["apache2-foreground"]
