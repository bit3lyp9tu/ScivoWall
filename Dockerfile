FROM php:apache

# Enable the Apache rewrite module
RUN a2enmod rewrite

# Set the port for Apache to listen on
ENV APACHE_PORT 8080
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Install necessary dependencies
RUN apt-get update
RUN apt-get install -y libssl-dev
RUN apt-get install -y iproute2 
RUN apt-get install -y iputils-ping 
#RUN apt-get install -y python3
#RUN apt-get install -y python3-pip 
#RUN apt-get install -y zip
#RUN docker-php-ext-install mysqli
#RUN docker-php-ext-install pdo pdo_mysql
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

RUN chown -R www-data:www-data /poster_generator_json

# Start Apache server
CMD ["apache2-foreground"]
