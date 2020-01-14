FROM php:5.6-apache

ENV OPENCART_VERSION 1.5.6.4

# Install curl, tar, mysqli, mcrypt, zip, gd for OpenCart.
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        tar \
        unzip \
        libmcrypt-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install gd \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

# Install and extract OpenCart from GitHub.
WORKDIR /var/www/html
RUN curl -L https://github.com/opencart/opencart/archive/$OPENCART_VERSION.tar.gz > opencart.tar.gz \
    && tar xf opencart.tar.gz \
    && mv opencart-$OPENCART_VERSION/upload/* . \
    && rm -rf opencart.tar.gz opencart-$OPENCART_VERSION/

# Prefill mySQL credentials
RUN sed -i -e "s/\['db_name'] = ''/['db_name'] = 'opencart'/g" install/controller/step_3.php \
    && sed -i -e "s/\['db_user'] = ''/['db_user'] = 'root'/g" install/controller/step_3.php \
    && sed -i -e "s/\['db_password'] = ''/['db_password'] = 'examplepass'/g" install/controller/step_3.php \
    && sed -i "s/localhost/database/g" install/controller/step_3.php

# Add Smaily plugin files to /html
ADD upload /var/www/html/

# Rename config.ph
RUN mv config-dist.php config.php \
    && mv admin/config-dist.php admin/config.php

RUN chown -R www-data:www-data /var/www \
    && find /var/www -type d -exec chmod 2750 {} \+ \
    && find /var/www -type f -exec chmod 640 {} \+

EXPOSE 80 3306
