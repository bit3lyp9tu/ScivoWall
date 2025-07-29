FROM php:8.2-apache

ENV APACHE_PORT=8080
ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV GECKO_VERSION=0.36.0

RUN apt update && \
    apt install -y mariadb-client \
    python3 python3-pip python3-venv \
    wget

RUN apt-get update && apt-get install -y \
    firefox-esr \
    libdbus-glib-1-2 \
    libgtk-3-0 \
    libasound2 \
    libx11-xcb1 \
    libdbus-1-3 \
    libxss1 \
    libnss3 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    xauth \
    x11-xserver-utils \
    xvfb && \
    rm -rf /var/lib/apt/lists/*

RUN wget https://github.com/mozilla/geckodriver/releases/download/v$GECKO_VERSION/geckodriver-v$GECKO_VERSION-linux64.tar.gz && \
    tar -xzf geckodriver-v${GECKO_VERSION}-linux64.tar.gz && \
    chmod +x geckodriver && \
    mv geckodriver /usr/local/bin/ && \
    rm geckodriver-v${GECKO_VERSION}-linux64.tar.gz

COPY tests/requirements.txt /tmp/requirements.txt

RUN python3 -m venv /venv && \
    /venv/bin/pip install --upgrade pip && \
    /venv/bin/pip install -r /tmp/requirements.txt

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

COPY . /var/www/html/

CMD ["apache2-foreground"]
