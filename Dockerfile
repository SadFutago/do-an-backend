FROM php:8.2-fpm

# Cài đặt các extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Sao chép mã nguồn và cài đặt dependencies
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoload
RUN php artisan config:cache
RUN php artisan route:cache

# Phân quyền cho thư mục storage và cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage

# Chạy server (Render sẽ điều khiển port)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]