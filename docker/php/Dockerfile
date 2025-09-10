FROM php:8.2-fpm-alpine

WORKDIR /var/www/app

# Instalar dependências do sistema
RUN apk add --no-cache \
    curl-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql curl gd zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar composer files
COPY composer.json composer.lock ./

# Instalar dependências do PHP
RUN composer install --no-dev --optimize-autoloader

# Copiar todo o código
COPY . .

# Configurar permissões
RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 755 /var/www/app

# Usar usuário www-data
USER www-data

# Expôr porta 9000
EXPOSE 9000

# Comando padrão
CMD ["php-fpm"]