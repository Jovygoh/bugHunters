FROM php:8.3-cli

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_AUDIT_ABORT=0

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_sqlite bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

# Auto-detect if context is root and bring Backend contents to /app root
RUN if [ -f /app/Backend/composer.json ]; then cp -rn /app/Backend/* /app/ 2>/dev/null || true; fi

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-security-blocking --ignore-platform-reqs

RUN mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache database \
    && touch database/database.sqlite \
    && cp -n .env.example .env 2>/dev/null || true \
    && chmod -R 777 storage bootstrap/cache database .env

EXPOSE 10000

CMD ["sh", "-c", "cp -n .env.example .env 2>/dev/null || true && php artisan key:generate --force && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]
