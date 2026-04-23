FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    zip unzip git curl

# Node 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    mbstring \
    opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Backend
RUN composer install --optimize-autoloader --no-interaction

# Frontend
RUN node -v
RUN npm -v
RUN npm install
RUN npm run build
RUN ls -R public/build

EXPOSE 8080

CMD ["/bin/sh", "-c", "php artisan config:clear && php artisan migrate --force && php artisan db:seed --force && php -S 0.0.0.0:8080 -t public public/index.php"]