FROM php:8.3-fpm

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN pecl install xdebug-3.3.1
RUN apt-get update
RUN apt-get install -y libzip-dev unzip
RUN docker-php-ext-install pdo_mysql

EXPOSE 9000

CMD php-fpm
