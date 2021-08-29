FROM php:7.4-apache
RUN apt-get update && apt-get upgrade -y
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install xdebug-2.9.1
RUN docker-php-ext-enable xdebug
RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/php.ini

#EXPOSE 80