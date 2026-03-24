FROM php:8.3-fpm

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    nodejs \
    npm

# Extensões PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 👇 ADICIONE AQUI
COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www

CMD ["php-fpm"]