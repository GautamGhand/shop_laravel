FROM php:8.2-cli

WORKDIR /app
COPY . .

RUN apt-get update && apt-get install -y \
    libpq-dev unzip git curl \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

CMD php -S 0.0.0.0:10000 -t public