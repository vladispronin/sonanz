FROM php:8.4-fpm-alpine AS base

WORKDIR /app

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache chromaprint supervisor \
    && mkdir -p /app/var/log/supervisor /app/var/run

RUN install-php-extensions \
    pdo_pgsql \
    amqp \
    intl \
    opcache \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize \
    && touch .env \
    && APP_ENV=prod \
       DATABASE_URL="postgresql://x:x@x:5432/x?serverVersion=16" \
       RABBITMQ_DSN="amqp://x:x@x:5672/%2f" \
       REDIS_URL="redis://x:6379" \
       APP_SECRET="build" \
       php bin/console cache:warmup --env=prod --no-optional-warmers \
    && chown -R www-data:www-data /app/var

FROM base AS download

COPY --from=denoland/deno:bin /deno /usr/local/bin/deno

RUN apk add --no-cache python3 py3-pip ffmpeg \
    && pip3 install --break-system-packages yt-dlp
