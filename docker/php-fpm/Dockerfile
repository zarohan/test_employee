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

RUN pecl install xdebug && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


# add aliases to on/off xdebug
# disabled by default
RUN mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.off
RUN echo 'alias xon="mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.off /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && kill -USR2 1"' >> ~/.bashrc
RUN echo 'alias xoff="mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.off && kill -USR2 1"' >> ~/.bashrc
RUN /bin/bash -c "source ~/.bashrc"
