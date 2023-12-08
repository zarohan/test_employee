FROM php:8.1-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    apt-utils \
    ssh-client \
    git \
    zip \
    curl \
    unzip \
    mc

RUN docker-php-ext-install pdo pdo_mysql

RUN  curl -sS https://get.symfony.com/cli/installer | bash \
     && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

run ./start.sh
