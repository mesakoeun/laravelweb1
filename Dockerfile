# ---------- Stage 1: build frontend assets (Vite) ----------
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ---------- Stage 2: install PHP dependencies ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader \
    --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ---------- Stage 3: PHP-FPM application image ----------
FROM php:8.3-fpm-alpine AS app

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN apk add --no-cache su-exec \
    && install-php-extensions pdo_mysql mbstring zip intl bcmath opcache pcntl redis

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ---------- Stage 4: Caddy web server image ----------
FROM caddy:2-alpine AS web
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --from=app /var/www/html/public /var/www/html/public
