FROM php:apache

# Enable the Apache rewrite module
RUN a2enmod rewrite

# Set the port for Apache to listen on
ENV APACHE_PORT 8080
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Install necessary dependencies
RUN apt-get update
RUN apt-get install -y iproute2 iputils-ping libssl-dev
RUN rm -rf /var/lib/apt/lists/*

# Copy the PHP files to the container
COPY . $APACHE_DOCUMENT_ROOT/
COPY .env /var/www/html/.env

# Configure Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN rm .env

# Expose the Apache port
EXPOSE $APACHE_PORT

RUN chmod 777 -R /tmp && chmod o+t -R /tmp

RUN mkdir -p /poster_generator_json/

RUN chown -R www-data:www-data /poster_generator_json

# RUN chown www-data:www-data ./json/*      ???

# Start Apache server
CMD ["apache2-foreground"]
