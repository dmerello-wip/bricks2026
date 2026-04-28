###############################################################################
# Stage 1 — php-base: Composer deps + generated assets for the frontend build
###############################################################################
FROM php:8.4-cli-alpine AS php-base

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg && \
    docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        exif \
        pcntl \
        zip \
        bcmath \
        intl \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-req=ext-pcntl

COPY . .

RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --classmap-authoritative

# Generate translation JSON files needed by the frontend build (gitignored)
RUN php artisan translation-handler:import --force --fresh

# Generate Wayfinder TypeScript files needed by the frontend build (gitignored)
RUN php artisan wayfinder:generate

###############################################################################
# Stage 2 — node-builder: Vite build (SSR mode)
###############################################################################
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --include=optional

COPY . .

# Bring in generated files from the PHP stage (all are gitignored)
COPY --from=php-base /app/resources/js/lang ./resources/js/lang
COPY --from=php-base /app/resources/js/actions ./resources/js/actions
COPY --from=php-base /app/resources/js/routes ./resources/js/routes
COPY --from=php-base /app/resources/js/wayfinder ./resources/js/wayfinder

# Build client bundle + SSR bundle
RUN npm run build:ssr

###############################################################################
# Stage 3 — runtime: php:8.4-apache (web server)
###############################################################################
FROM php:8.4-apache AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        exif \
        pcntl \
        zip \
        bcmath \
        intl \
        opcache \
    && rm -rf /var/lib/apt/lists/*

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN a2enmod rewrite headers

RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    DocumentRoot /var/www/html/public'; \
    echo '    <Directory /var/www/html/public>'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '        Options -Indexes +FollowSymLinks'; \
    echo '    </Directory>'; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
} > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY --from=php-base --chown=www-data:www-data /app .
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Publish Twill admin assets from vendor (no npm required)
RUN php artisan twill:build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]

###############################################################################
# Stage 4 — ssr: Node SSR server
###############################################################################
FROM node:22-alpine AS ssr

WORKDIR /app

COPY --from=node-builder /app/bootstrap/ssr ./bootstrap/ssr

EXPOSE 13714

CMD ["node", "bootstrap/ssr/ssr.js"]
