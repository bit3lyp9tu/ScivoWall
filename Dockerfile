# FROM php:apache
# # Enable the Apache rewrite module
# RUN a2enmod rewrite
# # Set the port for Apache to listen on
# ENV APACHE_PORT 8080
# ENV APACHE_DOCUMENT_ROOT /var/www/html
# # Install necessary dependencies
# RUN apt-get update
# RUN apt-get install -y iproute2 iputils-ping libssl-dev
# RUN rm -rf /var/lib/apt/lists/*
# # Copy the PHP files to the container
# COPY . $APACHE_DOCUMENT_ROOT/
# COPY .env /var/www/html/.env
# # Configure Apache
# RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
# RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
# RUN rm .env
# # Expose the Apache port
# EXPOSE $APACHE_PORT
# RUN chmod 777 -R /tmp && chmod o+t -R /tmp
# RUN mkdir -p /poster_generator_json/
# RUN chown -R www-data:www-data /poster_generator_json
# # RUN chown www-data:www-data ./json/*      ???
# # Start Apache server
# CMD ["apache2-foreground"]

FROM php:8.2-apache

ENV APACHE_PORT 1112
ENV APACHE_DOCUMENT_ROOT /home/runner/work/scientific_poster_generator/scientific_poster_generator/

RUN apt update && apt install -y mariadb-client

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

#RUN apt update
#RUN apt install -y mariadb-client

COPY . /home/runner/work/scientific_poster_generator/scientific_poster_generator/

#RUN echo $'Package: *\nPin: release o=LP-PPA-mozillateam\nPin-Priority: 1001' > /etc/apt/preferences.d/mozilla-firefox
#RUN add-apt-repository -y ppa:mozillateam/ppa
#RUN apt install -y firefox libx11-xcb-dev libxcb1-dev libdbus-1-3 libxtst6 libnss3 libgdk-pixbuf2.0-0 python3 python3-pip

#RUN apt install -y libx11-xcb-dev libxcb1-dev libdbus-1-3 libxtst6 libnss3 libgdk-pixbuf2.0-0 python3 python3-pip

#RUN python3 -m pip install --upgrade pip \
#    pip install --upgrade selenium \
#    pip install --upgrade mypy \
#    GECKO_VERSION=$(curl -s https://api.github.com/repos/mozilla/geckodriver/releases/latest | grep -o '"tag_name":"[^"]*"' | sed 's/"tag_name":"//;s/"$//') \
#    echo $GECKO_VERSION \
#    [[ $GECKO_VERSION='' ]] && GECKO_VERSION='v0.36.0' \
#    wget https://github.com/mozilla/geckodriver/releases/download/$GECKO_VERSION/geckodriver-$GECKO_VERSION-linux64.tar.gz \
#    tar -xzf geckodriver-$GECKO_VERSION-linux64.tar.gz \
#    chmod +x geckodriver

#RUN docker pull mariadb:latest \
#    docker save -o $HOME/cache/.docker/image.tar mariadb:latest

#RUN mariadb -hdbname -uroot -e "GRANT ALL PRIVILEGES ON poster_generator.* TO 'poster_generator'@'%' IDENTIFIED BY 'password'; FLUSH PRIVILEGES;"
#RUN mariadb -e "CREATE DATABASE IF NOT EXISTS poster_generator;"
#RUN mariadb poster_generator < ./tests/test_config2.sql


#RUN apt update && apt install -y -qq npm

#RUN npm install --global bower

#RUN bower install carousel-3d

# ENTRYPOINT ["sh"]
CMD ["apache2-foreground"]
