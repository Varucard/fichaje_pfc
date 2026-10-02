FROM php:8.4-apache

# Extensiones de PHP y cliente MySQL (mysqldump para los respaldos)
RUN apt-get update \
  && apt-get install -y --no-install-recommends unzip default-mysql-client libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
  && docker-php-ext-configure gd --with-jpeg --with-freetype \
  && docker-php-ext-install pdo_mysql gd \
  && rm -rf /var/lib/apt/lists/*

# Xdebug solo si se construye con --build-arg XDEBUG=1
ARG XDEBUG=0
RUN if [ "$XDEBUG" = "1" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache sirve únicamente la carpeta public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
  && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
  && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
  && a2enmod rewrite

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --no-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev \
  && chown -R www-data:www-data storage

COPY docker/entrypoint.sh /usr/local/bin/pfc-entrypoint
RUN chmod +x /usr/local/bin/pfc-entrypoint
ENTRYPOINT ["pfc-entrypoint"]
CMD ["apache2-foreground"]
