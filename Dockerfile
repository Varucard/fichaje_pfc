FROM php:8.4-apache

# Instala dependencias necesarias para PHP
RUN apt-get update && apt-get install -y \
    libpq-dev libonig-dev libzip-dev unzip

# Instala extensiones de PHP
RUN docker-php-ext-install pdo pdo_mysql

# Instala Xdebug y lo habilita
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configura Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Activa mod_rewrite de Apache
RUN a2enmod rewrite

CMD ["apache2-foreground"]
