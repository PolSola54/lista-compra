# ====================
# Etapa 1: Build de assets con Node
# ====================
FROM node:20-alpine AS assets-build

WORKDIR /app

# Copiamos solo lo necesario para instalar dependencias (mejor cache)
COPY package*.json ./
RUN npm ci

# Copiamos el resto del código
COPY . .

# Construimos los assets para producción
RUN npm run build

# ====================
# Etapa 2: Imagen final PHP + Nginx
# ====================
FROM richarvey/nginx-php-fpm:3.1.6-php8.3

# Copiamos todo el código Laravel
COPY --chown=www-data:www-data . /var/www/html

# Copiamos solo los assets ya compilados (lo más importante)
COPY --from=assets-build --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Instalamos dependencias de Composer (producción)
RUN composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# Permisos típicos de Laravel
RUN php artisan storage:link && \
    php artisan view:cache && \
    php artisan config:cache && \
    php artisan route:cache

# Variables recomendadas para Render
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr