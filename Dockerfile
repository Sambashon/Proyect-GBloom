FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev zlib1g-dev \
  && docker-php-ext-install zip pdo_mysql \
  && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Activar módulos Apache
RUN a2enmod rewrite ssl

# Configurar Apache
COPY apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Crear carpeta /php/vendor dentro de la imagen
RUN mkdir -p /php/vendor

# Instalar PHPMailer en /php/vendor
WORKDIR /php
RUN echo '{ "require": { "phpmailer/phpmailer": "^6.8" } }' > composer.json
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --working-dir=/php

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html /php

# Exponer puertos
EXPOSE 80 443
