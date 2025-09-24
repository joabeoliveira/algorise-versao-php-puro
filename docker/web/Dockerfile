FROM nginx:alpine
WORKDIR /var/www/app
COPY . /var/www/app
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Criar o diretório da aplicação
RUN mkdir -p /var/www/app

# Definir permissões adequadas
RUN chown -R nginx:nginx /var/www/app \
    && chmod -R 755 /var/www/app

# O diretório da aplicação será montado via volume no docker-compose.yml