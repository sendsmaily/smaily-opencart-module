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
    # Install OpenCart through the CLI installer.
    php cli_install.php install --db_hostname database \
        --db_username root \
        --db_password smailydev1 \
        --db_database opencart \
        --db_driver mysqli \
        --db_port 3306 \
        --username admin \
        --password smailydev1 \
        --email testing@smaily.sb \
        --agree_tnc yes \
        --http_server http://127.0.0.1:8080/
    rm -rd ../install/
fi
echo 'OpenCart installed!'

if [ ! -d $OC_ROOT_DIR/.modman ] ; then
    cd $OC_ROOT_DIR && modman init
fi
# Symlink Smaily's module files to html/
modman link /var/www/html/smailyfiles
docker-php-entrypoint "$@"
