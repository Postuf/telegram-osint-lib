FROM php:7.4-cli

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
&& docker-php-ext-install sockets \
&& docker-php-ext-install pcntl \
&& docker-php-ext-install bcmath \
&& docker-php-ext-configure gmp \
&& docker-php-ext-install gmp

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer

COPY examples /app/examples/
COPY src /app/src/
COPY composer.json /app/
COPY composer.lock /app/

WORKDIR /app

RUN composer update