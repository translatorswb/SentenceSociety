#!/usr/bin/env bash
git pull
yarn
yarn build
php bin/console cache:clear
chown -R www-data:www-data var public
