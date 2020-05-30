#!/bin/bash

rm -rf /var/www/html
ln -s /var/www/public /var/www/html

php-fpm
