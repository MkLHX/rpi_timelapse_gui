#!/bin/bash
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

## install dependencies
sudo apt-get update && sudo apt-get install git lighttpd php php-fpm php-cgi php-xml php-curl php-gd php-sqlite3 composer fswebcam ftp -y

## configure lighttpd server
# enable lighttpd
sudo lighttpd-enable-mod fastcgi-php
sudo service lighttpd force-reload
sudo service lighttpd restart
#sudo systemctl restart lighttpd.service || install_error "Unable to restart lighttpd"


## Deploy Symfony project
# save current html folder
sudo cp /var/www/html /var/www/html.save
# create symlink from project to http folder
# TODO use current user localtion
sudo ln -s /home/pi/rpi_timelapse_gui/public /var/www/html
# fix ownership
sudo chown -R www-data:www-data /var/www/html
#sudo chown -R www-data:www-data /home/pi/rpi_timelapse_gui/public

# edit .env.local with db name and path
sudo bash -c 'cat > .env.local' << EOF
APP_ENV=prod
APP_SECRET=mysecretisawesome
DATABASE_URL="sqlite:///%kernel_project_dir%/var/timelapse.db"
EOF

## deploy symfony project
# install project dependencies
composer install
# create database
php bin/console d:d:c
# make migrationd
php bin/console make:migration
# force u^date db schema
php bin/console d:s:u --force
# clear symfony cache
php bin/console c:c
# fix mode on var
sudo chmod -R 777 var
# restart http server
sudo service lighttpd restart

# set hostname
hostname="timelapse"
current_hostname=$(hostname)
if [ "$current_hostname" != "$hostname" ]
then
    echo "Change the current device hostname: $current_hostname by: $hostname"
    sudo hostname "$hostname"
    sudo sed -i "s/$current_hostname/$hostname/g" /etc/hosts
    sudo sed -i "s/$current_hostname/$hostname/g" /etc/hostname
    echo "Device will reboot in 5sec..."
    sleep 5
    sudo reboot
fi