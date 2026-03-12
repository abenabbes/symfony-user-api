FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000