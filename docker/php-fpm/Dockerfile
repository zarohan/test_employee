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
