FROM composer:2 AS composer

FROM php:8.1-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev unzip git \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_sqlite

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app
