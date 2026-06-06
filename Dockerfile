FROM php:8.2-cli

# Install system deps + MongoDB extension
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev \
    libxml2-dev libssl-dev pkg-config autoconf make g++ \
    && docker-php-ext-install zip mbstring \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Node.js 20 for Vite / Inertia.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN mkdir -p bootstrap/cache storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R 777 bootstrap/cache storage

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Build Vue.js / Inertia.js frontend assets
RUN npm install && npm run build

EXPOSE 8000

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
