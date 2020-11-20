#!/bin/sh

# Wait for MySQL to start.
mysql_ready() {
    mysqladmin ping --host=$DB_HOST --user=$DB_USER --password=$DB_PASSWORD > /dev/null 2>&1
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
        --db_host $DB_HOST \
        --db_user $DB_USER \
        --db_password $DB_PASSWORD \
        --db_name opencart \
        --db_port 3306 \
        --username admin \
        --password smailydev1 \
        --email admin@smaily.sandbox \
        --agree_tnc yes \
        --http_server http://localhost:8080/

    # Remove install directory.
    rm -rd ./install
fi

# Link module files to OpenCart installation.
if [ ! -d ./.modman ]; then
    su www-data -s /bin/bash -c "modman init"
fi
su www-data -s /bin/bash -c "modman link /smaily_for_opencart"

docker-php-entrypoint "$@"
