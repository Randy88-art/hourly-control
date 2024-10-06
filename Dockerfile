FROM php:8.2-apache

ARG TIMEZONE="Europe/Madrid"

ARG USER_ID=1000
ARG GROUP_ID=1000

# Set working directory
WORKDIR /var/www

COPY / /var/www/

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini
RUN "date"

# Asigna grupo y usuario en contenedor para no tener que estar cambiando propietario a los archivos creados desde el contenedor
RUN addgroup --gid ${GROUP_ID} mario
RUN adduser --disabled-password --gecos '' --uid ${USER_ID} --gid ${GROUP_ID} mario

# Install system dependencies
RUN apt-get update && apt-get install -y git unzip zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install PHP extensions Type docker-php-ext-install to see available extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && docker-php-ext-install pdo_mysql gd  

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure virtual host
RUN mv /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.old
RUN mv /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf.old
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

# Config files for php.ini and apache2.conf
RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini-development.old
RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini-production.old
COPY /php_conf/php.ini-development /usr/local/etc/php/
COPY /php_conf/php.ini-production /usr/local/etc/php/
COPY /apache_conf/apache2.conf /etc/apache2

USER 1000



