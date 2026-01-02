FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip opcache gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /www/var/htm

COPY composer.json composer.lock ./

RUN composer install --optimize-autoloader --no-dev --no-interaction

COPY . .

EXPOSE 9000

CMD ["php-fpm"]
