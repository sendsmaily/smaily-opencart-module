FROM php:7.3-apache

ENV OPENCART_VERSION 2.3.0.2

# Install curl, tar, mysqli, mcrypt, zip, gd for OpenCart.
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        tar \
        libmcrypt-dev \
        zlib1g-dev \
        libzip-dev \
        libfreetype6-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        mariadb-client \
    && pecl install mcrypt-1.0.3 \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-ext-install gd \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Install and extract OpenCart from GitHub to /html
WORKDIR /var/www/html
RUN curl -L https://github.com/opencart/opencart/archive/$OPENCART_VERSION.tar.gz > opencart.tar.gz \
    && tar xf opencart.tar.gz \
    && mv opencart-$OPENCART_VERSION/upload/* . \
    && rm -rf opencart.tar.gz opencart-$OPENCART_VERSION/

RUN mv config-dist.php config.php \
    && mv admin/config-dist.php admin/config.php

RUN chown -R www-data:www-data /var/www \
    && find /var/www -type d -exec chmod 2750 {} \+ \
    && find /var/www -type f -exec chmod 640 {} \+

# Install modman
RUN curl -SL https://raw.githubusercontent.com/colinmollenhour/modman/master/modman -o modman \
    && chmod +x ./modman \
    && mv ./modman /usr/local/bin/

# Entrypoint for symlinking Smaily plugin files & OpenCart CLI install
ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

CMD ["apache2-foreground"]
EXPOSE 80 3306
