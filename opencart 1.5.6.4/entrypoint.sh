#!/bin/sh
mysql_ready() {
    mysqladmin ping --host=database --user=root --password=smailydev1 > /dev/null 2>&1
}
if [ -d install ] ; then
    while !(mysql_ready)
    do
        sleep 1
        echo "Waiting for MySQL to finish..."
    done
    cd install/
    # OC 1.5 cli_install.php defaults to mysql even when using --db_driver
    sed -i -e 's/\x27mysql\\/\x27mysqli\\/g' cli_install.php
    # Install OpenCart through the CLI installer.
    php cli_install.php install --db_host database \
        --db_user root \
        --db_password smailydev1 \
        --db_name opencart \
        --db_port 3306 \
        --username admin \
        --password smailydev1 \
        --email testing@smaily.sb \
        --agree_tnc yes \
        --http_server http://127.0.0.1:8080/
    rm -rd ../install/
fi
echo 'OpenCart installed!'

if [ ! -d $MAGE_ROOT_DIR/.modman ] ; then
    cd $MAGE_ROOT_DIR && modman init
fi
# Symlink Smaily's module files to html/
modman link /var/www/html/smailyfiles
docker-php-entrypoint "$@"
