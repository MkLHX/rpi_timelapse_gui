#!/bin/bash
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

## install dependencies
sudo apt-get update && sudo apt-get install git php php-fpm php-cgi php-xml php-curl php-gd php-sqlite3 composer fswebcam ftp yarn -y

# apache2 settings
cat > /etc/apache2/sites-available/timelapse.conf << EOF
<VirtualHost *:80>
        #ServerName timelapse.local
        #ServerAlias timelapse.local

        #ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html

        ErrorLog ${APACHE_LOG_DIR}/timelapse.error.log
        #CustomLog ${APACHE_LOG_DIR}/timelapse.access.log combined

        <Directory /var/www/html>
                AllowOverride None
                Order Allow,Deny
                Allow from All
                FallbackResource /index.php
        </Directory>
        <Directory /var/www/html/public/bundles>
                FallbackResource disabled
        </Directory>

</VirtualHost>
EOF
sudo systemctl enable apache2.service
sudo systemctl start apache2.service

## Deploy Symfony project
# save current html folder
sudo cp -r /var/www/html /var/www/html.save
# create symlink from project to http folder
sudo ln -s public /var/www/html
# fix ownership
sudo chown -R www-data:www-data /var/www/html

# edit .env.local with db name and path
sudo bash -c 'cat > .env.local' << EOF
APP_ENV=prod
APP_SECRET=mysecretisawesome
DATABASE_URL="sqlite:///%kernel.project_dir%/var/timelapse.db"
EOF

## deploy symfony project
# install project dependencies
composer install
# create database
php bin/console d:d:c
# force update db schema
php bin/console d:s:u --force
# clear symfony cache
php bin/console c:c
# fix mode on var
sudo chmod -R 777 var
# install front assest
yarn install
# build js and css
yarn encore production
# restart http server
sudo service apache2 restart

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