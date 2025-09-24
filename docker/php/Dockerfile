FROM php:8.2-fpm-alpine

WORKDIR /var/www/app

RUN apk add --no-cache \
    curl-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql curl gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader

COPY . .

EXPOSE 9000

CMD ["php-fpm"]