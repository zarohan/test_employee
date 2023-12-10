**Rest Api**

**Instalation**

``
docker-compose up -d
``

``
docker-compose exec php-fpm bash "/var/www/html/setup.sh"
``

This command will run ./setup.sh script which will install all dependencies and apply migrations.

After that you can access the swagger at http://127.0.0.1/restapi

