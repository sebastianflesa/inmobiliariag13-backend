FROM php:8.2-cli-alpine

# Runtime libs mínimas (no -dev)
RUN apk add --no-cache \
    libpq \
    libzip \
    zlib \
    oniguruma \
    git curl unzip zip

# Build deps y headers (sí -dev, y toolchain)
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    postgresql-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    pkgconf

WORKDIR /var/www/html

# Composer
RUN curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer

# ---- Extensiones (una por una para aislar fallos) ----
# bcmath
RUN set -eux; docker-php-ext-install -j"$(nproc)" bcmath

# mbstring (en Alpine a veces necesita oniguruma-dev presente)
RUN set -eux; docker-php-ext-install -j"$(nproc)" mbstring

# pdo_pgsql (requiere postgresql-dev)
RUN set -eux; docker-php-ext-install -j"$(nproc)" pdo_pgsql

# zip: intenta oficial; si falla, usa PECL
RUN set -eux; \
  docker-php-ext-configure zip || true; \
  if ! docker-php-ext-install -j"$(nproc)" zip; then \
    pecl install zip && docker-php-ext-enable zip; \
  fi

# Opcional: quitar build-deps para achicar imagen (si todo compiló)
RUN apk del .build-deps || true

# Cache deps PHP (si hay composer.*)
COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-interaction --prefer-dist || true

# Código
COPY . .

# Permisos Laravel
RUN mkdir -p storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# Arranque simple para dev
CMD sh -lc '\
  if [ ! -d vendor ]; then composer install --no-interaction --prefer-dist; fi && \
  if [ ! -f .env ]; then cp .env.example .env || true; fi && \
  if ! grep -q "^APP_KEY=" .env 2>/dev/null || [ -z "$(grep "^APP_KEY=" .env | cut -d= -f2)" ]; then \
    php artisan key:generate; \
  fi && \
  php artisan serve --host=0.0.0.0 --port=8000'
