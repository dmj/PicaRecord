FROM php:7.2-cli
COPY . /usr/src/picarecord
WORKDIR /usr/src/picarecord
CMD "./vendor/bin/phpunit"
