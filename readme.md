# RaspberryPi TimeLapse GUI

## Why this project?
Because i need a timelapse solution who people can use it and install it easyly on raspberry pi and to test the new Symfony 5.


## How it's work?
Just set timelapse parameters and save it. 

Behind the scene a cron task will take a picture by using your settings.

## Tested on
- Raspberry Pi 3B+
- Raspberry Pi 3A
### Should Works on every Raspberry Pi with raspbian distribution

## How to quick install it?
### clone the repo
```bash
git clone https://github.com/MkLHX/rpi_timelapse_gui.git

cd rpi_timelapse_gui
```
Just run:
```bash
bash installer.sh
```

## Manual installation
In manual installation, we assume:
- you have a http webserver like Apache2 / Nginx or Lighttpd and you manage configuration
- you have every dependencies installed 
> sudo apt-get install git lighttpd php php-fpm php-cgi php-xml php-curl php-gd php-sqlite3 composer fswebcam ftp -y


### symlink public project folder to you webserver
```bash
sudo ln -s /home/pi/rpi_timelapse_gui/public /var/www/timelapse
```
### give right ownership
```bash
sudo chown -R www-data:www-data /var/www/timelapse
```

### copy .env to .env.local and edit
```bash
APP_ENV=prod
APP_SECRET=mysecretisawesome
DATABASE_URL="sqlite:///%kernel.project_dir%/var/timelapse.db"
```
### install project dependencies
```bash
composer install
```
### create database
```bash
php bin/console d:d:c
```
### make migrations
```bash
php bin/console make:migration
```
### force update db schema
```bash
php bin/console d:s:u --force
```
### clear symfony cache
```bash
php bin/console c:c
```
### fix mode on var
```bash
sudo chmod -R 777 var
```

### Developped by using [Symfony](https://github.com/symfony) 5