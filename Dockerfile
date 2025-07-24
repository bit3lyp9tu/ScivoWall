FROM php:8.2-apache

ENV APACHE_PORT=8080
ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV RUNNING_IN_DOCKER=true

RUN apt update && \
    apt install -y mariadb-client python3 python3-pip python3-venv

RUN python3 -m venv /venv

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

CMD ["apache2-foreground"]
