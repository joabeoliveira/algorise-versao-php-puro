FROM php:8.2-fpm-alpine

WORKDIR /var/www/app

RUN apk add --no-cache \
    curl-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev

RUN docker-php-ext-install pdo pdo_mysql curl gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia os arquivos do composer do contexto raiz
COPY ../../composer.json ../../composer.lock ./

RUN composer install --no-dev --optimize-autoloader

# Copia todo o conteúdo do contexto raiz
COPY ../../ ./

EXPOSE 9000