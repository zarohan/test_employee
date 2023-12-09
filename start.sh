php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

mv composer.phar /usr/local/bin/composer

composer install

curl -sS https://get.symfony.com/cli/installer | bash
mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

symfony console doctrine:database:create --if-not-exists
symfony console doctrine:migrations:migrate --no-interaction

symfony console --env=test doctrine:database:create --if-not-exists
symfony console --env=test doctrine:schema:create --no-interaction
