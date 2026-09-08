# ==========================================
# Stage 1: Build Frontend Assets (Vite / Vue)
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
ENV SKIP_WAYFINDER=true
RUN npm run build

# ==========================================
# Stage 2: Install Composer Dependencies
# ==========================================
FROM composer:2 AS vendor
WORKDIR /app

COPY composer*.json ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .
RUN composer dump-autoload --optimize

# ==========================================
# Stage 3: Production Runtime (FrankenPHP)
# ==========================================
FROM dunglas/frankenphp:1-php8.4-bookworm AS runner

# Install required PHP extensions for Laravel and Supabase PostgreSQL
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    bcmath \
    pcntl \
    intl \
    zip \
    opcache \
    redis

WORKDIR /app

# Copy application files
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Set permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENV SERVER_NAME=":80"
ENV APP_ENV=production
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
