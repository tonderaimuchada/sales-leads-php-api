FROM php:8.2-cli-alpine
WORKDIR /app

# Install system deps + pdo_pgsql
RUN apk add --no-cache libpq-dev curl && \
    docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy and install deps
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy source
COPY . .

EXPOSE 8081

CMD ["php", "-S", "0.0.0.0:8081", "-t", "public"]
