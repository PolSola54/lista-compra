# ================
# 1. Build de assets (Vite)
# ================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ================
# 2. Imagen final PHP 8.3 + Nginx
# ================
FROM serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

# Copiamos el código del proyecto
COPY --chown=www-data:www-data . /var/www/html

# Sobrescribimos los assets ya compilados
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Composer en producción
RUN composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# Optimizaciones Laravel
RUN php artisan storage:link && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan optimize:clear && \
    php artisan optimize

# Variables de entorno
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV PHP_OPCACHE_ENABLE=1

# ← Mejora brutal de rendimiento en producción
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0