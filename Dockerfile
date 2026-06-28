FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --no-dev --optimize-autoloader

RUN cp .env.example .env
RUN php artisan key:generate

RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

COPY nginx.conf /etc/nginx/sites-available/default

EXPOSE 80

CMD service nginx start && php-fpm
