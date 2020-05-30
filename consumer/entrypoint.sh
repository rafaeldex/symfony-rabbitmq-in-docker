#!/bin/bash

rm -rf /var/www/html
ln -s /var/www/public /var/www/html

while ! nc -z rabbitmq 15672; do sleep 10; done
php /var/www/bin/console app:receive-messages &

php-fpm
