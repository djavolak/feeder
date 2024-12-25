ARG PHP_VERSION
ARG IMAGES_PATH
FROM php:${PHP_VERSION}-fpm
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y git unzip libssl-dev build-essential libxml2-dev zlib1g-dev libpng-dev libzip-dev libmagickwand-dev

RUN docker-php-ext-install pdo && docker-php-ext-enable pdo
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable pdo_mysql
RUN docker-php-ext-install simplexml && docker-php-ext-enable simplexml
RUN docker-php-ext-install ftp && docker-php-ext-enable ftp
RUN docker-php-ext-install gd && docker-php-ext-enable gd
RUN docker-php-ext-install intl && docker-php-ext-enable intl
RUN docker-php-ext-install zip && docker-php-ext-enable zip
RUN docker-php-ext-install opcache && docker-php-ext-enable opcache
RUN pecl install igbinary  && rm -rf /tmp/pear && docker-php-ext-enable igbinary
RUN pecl install imagick  && rm -rf /tmp/pear && docker-php-ext-enable imagick
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /app
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
RUN chmod 777 -R /public
