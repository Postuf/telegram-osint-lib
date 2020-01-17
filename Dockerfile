FROM php:7.2-fpm

LABEL version="1.0.0"
LABEL description="Postuf Telegram OSINT Library"

RUN apt-get update && apt-get install -y git \
zlib1g-dev \
libicu-dev \
libgmp-dev \
g++ \
libzip-dev \
unzip \
&& docker-php-ext-install zip \
&& docker-php-ext-configure gmp \
&& docker-php-ext-install gmp \
&& docker-php-ext-install sockets

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer \
&& composer global require hirak/prestissimo

ADD ./docker/php.ini /usr/local/etc/php
ADD ./docker/fpm-custom.conf /usr/local/etc/php-fpm.d/

WORKDIR /app

RUN git clone https://github.com/Postuf/telegram-osint-lib.git /app/ \
&& composer update