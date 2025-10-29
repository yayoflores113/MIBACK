FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos de la aplicación
COPY . .

# Instalar dependencias
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# Establecer permisos
RUN chown -R www-data:www-data /var/www

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000