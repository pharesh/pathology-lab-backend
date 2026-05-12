FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring

# Node.js for Vite/Vue.js asset build (required by Inertia.js)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R 777 bootstrap/cache storage

RUN composer install --no-dev --optimize-autoloader

# Build Vue.js / Inertia.js frontend assets
RUN npm install && npm run build

EXPOSE 8000

# migrate (safe) instead of migrate:fresh (destructive)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
