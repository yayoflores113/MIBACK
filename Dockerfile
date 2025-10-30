# Usar PHP 8.4 con Apache
FROM php:8.4-apache

# Habilitar módulos de Apache
RUN a2enmod rewrite headers proxy_http

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev libonig-dev \
    libcurl4-openssl-dev libicu-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql xml curl opcache intl

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copiar el código fuente
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar dependencias PHP (Filament, Laravel, etc.)
RUN composer install --no-dev --optimize-autoloader

# Permisos de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Configurar Apache para Laravel
COPY laravel.conf /etc/apache2/sites-available/laravel.conf
RUN a2dissite 000-default.conf \
    && a2ensite laravel.conf \
    && a2enmod rewrite

# Configurar límites de subida
RUN echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size=60M" >> /usr/local/etc/php/conf.d/uploads.ini

# Exponer puerto 80
EXPOSE 80

# Comando final
CMD ["apache2-foreground"]
