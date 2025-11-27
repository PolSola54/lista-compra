# ================
# 1. Build de assets (Vite)
# ================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci                  # o npm ci --omit=dev si quieres

COPY . .
RUN npm run build

# ================
# 2. Imagen final (PHP 8.3 + Nginx + todo optimizado)
# ================
FROM serversideup/php:8.3-fpm-nginx

# Carpeta del proyecto
WORKDIR /var/www/html

# Copiamos todo el código Laravel
COPY --chown=www-data:www-data . /var/www/html

# Sobrescribimos los assets ya compilados (importantísimo)
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Composer (la imagen ya trae composer instalado)
RUN composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# Optimizaciones Laravel
RUN php artisan storage:link && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan optimize:clear && \
    php artisan optimize

# Variables recomendadas (puedes sobrescribirlas en Render si quieres)
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0   # ← importante en producción