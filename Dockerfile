# ============================================
# Dockerfile para Producción - Inferno Club
# Laravel + PHP-FPM (Sin Nginx - NPM externo)
# ============================================

FROM php:8.3-fpm-alpine

LABEL maintainer="OppaDev <leonardo.obando@oppadev.com>"
LABEL description="Inferno Club - Sistema de Gestión"
LABEL version="1.0.0"

# Variables de entorno
ENV TZ=America/Guayaquil \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_MEMORY_LIMIT=256M

# ============================================
# Instalar dependencias del sistema
# ============================================
RUN apk add --no-cache \
    postgresql-dev \
    postgresql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    bash \
    git \
    curl \
    nodejs \
    npm \
    tzdata

# Configurar timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# ============================================
# Instalar extensiones PHP
# ============================================
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    gcc \
    g++ \
    make \
    $PHPIZE_DEPS && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache && \
    docker-php-ext-enable opcache && \
    apk del .build-deps

# ============================================
# Configuración PHP
# ============================================
RUN echo "memory_limit = ${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory.ini && \
    echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/memory.ini && \
    echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/memory.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/memory.ini && \
    echo "date.timezone = ${TZ}" >> /usr/local/etc/php/conf.d/memory.ini

# Configuración OPcache
RUN echo "opcache.enable=${PHP_OPCACHE_ENABLE}" > /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# ============================================
# Instalar Composer
# ============================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============================================
# Crear usuario y directorio de trabajo
# ============================================
RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel

WORKDIR /var/www/html

# ============================================
# Copiar archivos de la aplicación
# ============================================
COPY --chown=laravel:laravel . .

# ============================================
# Instalar dependencias PHP
# ============================================
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# ============================================
# Instalar y compilar assets
# ============================================
RUN npm install && \
    npm run build && \
    rm -rf node_modules

# ============================================
# Preparar directorios y permisos
# ============================================
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache && \
    chown -R laravel:laravel storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# ============================================
# Crear enlace simbólico storage
# ============================================
RUN ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# ============================================
# Configurar archivo .env para producción
# ============================================
RUN cp .env.production .env && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# ============================================
# Limpiar archivos innecesarios
# ============================================
RUN rm -rf \
    tests \
    .git \
    .github \
    .env.example \
    README.md \
    /root/.composer \
    /root/.npm

# ============================================
# Cambiar a usuario no privilegiado
# ============================================
USER laravel

# Exponer puerto PHP-FPM
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php-fpm -t || exit 1

# Comando por defecto
CMD ["php-fpm"] 