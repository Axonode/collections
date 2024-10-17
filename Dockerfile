FROM php:8.2-cli-alpine3.20
LABEL authors="namenyi.janos@gmail.com"

WORKDIR /var/www

RUN apk add --update --no-cache linux-headers $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY --from=composer:2.7.8 /usr/bin/composer /usr/local/bin/composer
