# Docker
# Crear la imagen de Docker localmente: 
#`docker image build -t laravel-php8.1:latest .`
# Linux
#`docker run --name caribbean-transportation-api -p 1036:80 -v $(pwd):/var/www/html -d laravel-php8.1:latest`
# Windows
#`docker run --name caribbean-transportation-api -p 3062:80 -v "$(pwd):/var/www/html" -d laravel-php8.1:latest`
# En windows, si el pwd da problemas, ejecutar en consola pwd y copiar el path que genera, reemplazarlo por $(pwd)
# Ejemplo docker run --name caribbean-transportation-api -p 1036:80 -v "/c/laragon/www/eTransfer/laravel-etransfers:/var/www/html" -d laravel-php8.1:latest

FROM php:8.1-apache

RUN apt-get update
RUN apt-get install -y nano

RUN apt-get update && \
    apt-get install --yes --force-yes \
    cron g++ gettext libicu-dev openssl \
    libc-client-dev libkrb5-dev  \
    libxml2-dev libfreetype6-dev \
    libgd-dev libmcrypt-dev bzip2 \
    libbz2-dev libtidy-dev libcurl4-openssl-dev \
    libz-dev libmemcached-dev libxslt-dev git-core libpq-dev \
    libzip4 libzip-dev libwebp-dev \
    zip unzip

# PHP Configuration
RUN docker-php-ext-install bcmath bz2 calendar dba exif gettext iconv intl  soap tidy xsl zip&&\
    docker-php-ext-install mysqli pgsql pdo pdo_mysql pdo_pgsql  &&\
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp &&\
    docker-php-ext-install gd &&\
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl &&\
    docker-php-ext-install imap &&\
    docker-php-ext-configure hash --with-mhash &&\
    pecl install xdebug && docker-php-ext-enable xdebug &&\
    curl -sS https://getcomposer.org/installer | php \
            && mv composer.phar /usr/bin/composer

#Copiamos el archivo php.ini de producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

#Apuntamos la ruta del sitio a donde necesitemos...
#RUN echo "<VirtualHost *:80>\n ServerAdmin support@ocastholdings.com\n DocumentRoot /var/www/html/public\n ErrorLog ${APACHE_LOG_DIR}/error.log\n CustomLog ${APACHE_LOG_DIR}/access.log combined\n </VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf
#RUN echo "<VirtualHost *:443>\n ServerAdmin support@ocastholdings.com\n DocumentRoot /var/www/html/public\n ErrorLog ${APACHE_LOG_DIR}/error.log\n CustomLog ${APACHE_LOG_DIR}/access.log combined\n </VirtualHost>\n" >> /etc/apache2/sites-available/000-default.conf

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN chgrp -R www-data /var/www
RUN find /var/www -type d -exec chmod 775 {} +
RUN find /var/www -type f -exec chmod 664 {} +
CMD ["/usr/sbin/apache2ctl","-DFOREGROUND"]


WORKDIR /var/www/html
# RUN chmod -R 777 /storage/

EXPOSE 80
EXPOSE 443 