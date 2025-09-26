FROM php:8.3-cli

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /app"]
