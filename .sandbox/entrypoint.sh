#!/bin/sh

# Wait for MySQL to start.
mysql_ready() {
    mysqladmin ping --host=database --user=root --password=smailydev1 > /dev/null 2>&1
}
while !(mysql_ready); do
    sleep 1
    echo "Waiting for MySQL to finish start up..."
done

# Ensure OpenCart is installed.
if [ -d install ]; then
    # OC 1.5 cli_install.php defaults to mysql even when using --db_driver
    sed -i -e 's/\x27mysql\\/\x27mysqli\\/g' ./install/cli_install.php

    # Install OpenCart through the CLI installer.
    php ./install/cli_install.php install \
        --db_host database \
        --db_user root \
        --db_password smailydev1 \
        --db_name opencart \
        --db_port 3306 \
        --username admin \
        --password smailydev1 \
        --email testing@smaily.sandbox \
        --agree_tnc yes \
        --http_server http://127.0.0.1:8080/

    # Remove install directory.
    rm -rd ./install

    echo 'OpenCart installed!'
fi

# Link module files to OpenCart installation.
if [ ! -d ./.modman ]; then
    modman init
fi
modman link /var/www/html/smaily_for_opencart

docker-php-entrypoint "$@"
