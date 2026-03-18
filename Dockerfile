# syntax=docker/dockerfile:1

FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock* symfony.lock* ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM php:8.4-apache AS app

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    APP_SECRET=change-me-in-prod \
    DEFAULT_URI=http://localhost \
    CORS_ALLOW_ORIGIN=^https?://(localhost|127\\.0\\.0\\.1)(:[0-9]+)?$ \
    APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN mkdir -p var/cache var/log \
    && if [ ! -f .env ]; then printf "APP_ENV=prod\nAPP_DEBUG=0\nAPP_SECRET=change-me-in-prod\nDEFAULT_URI=http://localhost\nCORS_ALLOW_ORIGIN='^https?://(localhost|127\\.0\\.0\\.1)(:[0-9]+)?$'\n" > .env; fi \
    && chown -R www-data:www-data var

EXPOSE 80

CMD ["apache2-foreground"]
