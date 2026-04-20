FROM php:8.3-apache

RUN a2enmod rewrite

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

RUN sed -i 's|/var/www/html|/app/public|g' /etc/apache2/sites-available/000-default.conf

WORKDIR /app
COPY . .

RUN composer install --optimize-autoloader --no-scripts --no-interaction

EXPOSE 80

CMD php artisan config:clear && php artisan migrate --force && apache2-foreground