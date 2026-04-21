FROM php:8.3-cli

RUN apt-get update
RUN apt-get install -y libpq-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libfreetype6-dev
RUN apt-get install -y libzip-dev
RUN apt-get install -y libonig-dev
RUN apt-get install -y zip unzip git curl

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql
RUN docker-php-ext-install gd
RUN docker-php-ext-install zip
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --optimize-autoloader --no-scripts --no-interaction

EXPOSE 8080

RUN apt-get install -y nodejs npm
RUN npm install && npm run build

CMD ["/bin/sh", "-c", "php artisan migrate:fresh --seed --force && php -S 0.0.0.0:8080 -t public"]