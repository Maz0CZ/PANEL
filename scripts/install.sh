#!/usr/bin/env bash
set -euo pipefail

sudo apt-get update
sudo apt-get install -y \
  apache2 \
  libapache2-mod-php \
  php \
  php-cli \
  php-sqlite3 \
  php-intl \
  php-mbstring \
  php-xml \
  sqlite3 \
  screen \
  openjdk-17-jre-headless \
  unzip \
  curl

sudo a2enmod rewrite
sudo systemctl restart apache2

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

printf "\nHotovo. Pokračujte inicializací databáze.\n"
