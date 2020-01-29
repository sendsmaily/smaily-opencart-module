#!/bin/bash -x

if [ ! -d $MAGE_ROOT_DIR/.modman ] ; then
    cd $MAGE_ROOT_DIR && modman init
fi
# TODO: List all files in folder and create modman file based on that
modman link /var/www/html/smailyfiles
docker-php-entrypoint "$@"
