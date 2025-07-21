FROM php:8.2-apache

ENV APACHE_PORT 8080
ENV APACHE_DOCUMENT_ROOT /var/www/html

RUN apt update && apt install -y mariadb-client

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

CMD ["apache2-foreground"]
