FROM php:5.6-apache

ENV OPENCART_VERSION 2.2.0.0

# Install curl, tar, mysqli, mcrypt, zip, gd for OpenCart.
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        tar \
        libmcrypt-dev \
        zlib1g-dev \
        libzip-dev \
        libfreetype6-dev \
        libpng-dev \
        libjpeg-dev \
    && docker-php-ext-install mcrypt \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-ext-install gd \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

# Install and extract OpenCart from GitHub to /html
WORKDIR /var/www/html
RUN curl -L https://github.com/opencart/opencart/archive/$OPENCART_VERSION.tar.gz > opencart.tar.gz \
    && tar xf opencart.tar.gz \
    && mv opencart-$OPENCART_VERSION/upload/* . \
    && rm -rf opencart.tar.gz opencart-$OPENCART_VERSION/

# Prefill mySQL credentials
RUN sed -i -e "s/\['db_database'] = ''/['db_database'] = 'opencart'/g" install/controller/install/step_3.php \
    && sed -i -e "s/\['db_password'] = ''/['db_password'] = 'smaily1'/g" install/controller/install/step_3.php \
    && sed -i -e "s/\['password'] = ''/['password'] = 'smaily1'/g" install/controller/install/step_3.php \
    && sed -i -e "s/\['email'] = ''/['email'] = 'testing@smaily.sb'/g" install/controller/install/step_3.php \
    && sed -i "s/localhost/database/g" install/controller/install/step_3.php

RUN mv config-dist.php config.php \
    && mv admin/config-dist.php admin/config.php

RUN chown -R www-data:www-data /var/www \
    && find /var/www -type d -exec chmod 2750 {} \+ \
    && find /var/www -type f -exec chmod 640 {} \+

# Install modman
RUN curl -SL https://raw.githubusercontent.com/colinmollenhour/modman/master/modman -o modman \
    && chmod +x ./modman \
    && mv ./modman /usr/local/bin/

# Add modman wrapper for symlinking Smaily plugin files
ADD modman-wrapper.sh /modman-wrapper.sh
RUN chmod +x /modman-wrapper.sh
ENTRYPOINT ["/modman-wrapper.sh"]

CMD ["apache2-foreground"]
EXPOSE 80 3306
