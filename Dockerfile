###############################################################################
# Stage 1 — php-base: Composer deps + generated assets for the frontend build
###############################################################################
FROM php:8.4-cli-alpine AS php-base

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    nodejs \
    npm \
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

# Dummy values only for build-time artisan commands — overridden at runtime
# via Coolify env. CACHE/SESSION/QUEUE forzati a in-memory: senza un .env nel
# build context Laravel cadrebbe sui default (sqlite) e twill:build
# (-> twill:flush-manifest -> cache) crasherebbe.
ENV APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
ENV CACHE_STORE=array
ENV SESSION_DRIVER=array
ENV QUEUE_CONNECTION=sync

# Generate translation JSON files needed by the frontend build (gitignored)
RUN php artisan translation-handler:import --force --fresh

# Generate Wayfinder TypeScript files needed by the frontend build (gitignored)
RUN php artisan wayfinder:generate --with-form

# Build Twill admin assets — compila eventuali custom Vue blocks/components in
# resources/assets/js/{blocks,components}. Pulisce node_modules e dist subito
# dopo: a valle servono solo i file pubblicati in public/assets/twill/.
RUN php artisan twill:build \
    && rm -rf vendor/area17/twill/node_modules vendor/area17/twill/dist

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

# Build client bundle + SSR bundle (WAYFINDER_SKIP prevents the plugin from
# re-running php artisan wayfinder:generate — files are already copied above)
RUN WAYFINDER_SKIP=1 npm run build:ssr

###############################################################################
# Stage 3 — runtime: nginx + php-fpm (Alpine) managed by supervisord
###############################################################################
FROM php:8.4-fpm-alpine AS runtime

# Runtime libs + transient build deps for PHP extensions (removed at the end)
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        bash \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        icu-libs \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
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
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

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

# Run nginx as www-data so it shares ownership with php-fpm
RUN sed -i 's/user nginx;/user www-data;/' /etc/nginx/nginx.conf

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

COPY --from=php-base --chown=www-data:www-data /app .
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Dummy key only for build-time artisan commands — overridden at runtime via env
ENV APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=

# Twill admin assets sono già pubblicati in public/assets/twill/ dallo stage
# php-base (`twill:build`), e arrivano qui via il `COPY --from=php-base /app .`
# qui sopra. Niente da fare.

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/local/bin/entrypoint.sh"]

###############################################################################
# Stage 4 — ssr: Node SSR server
###############################################################################
FROM node:22-alpine AS ssr

WORKDIR /app

# Install production deps so the SSR bundle can resolve runtime imports
# (react, react-dom, @inertiajs/react, ecc. — Vite per default li externalize)
COPY package.json package-lock.json ./
RUN npm ci --omit=dev --omit=optional --ignore-scripts

COPY --from=node-builder /app/bootstrap/ssr ./bootstrap/ssr

EXPOSE 13714

CMD ["node", "bootstrap/ssr/ssr.js"]
