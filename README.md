**Rest Api**

**Instalation**

``
docker-compose up
``

This command will run ./start.sh script which will install all dependencies and apply migrations.

After that you can access the api on http://127.0.0.1/restapi/employee

**Routes:**
```
  restapi_employee_get       GET      ANY      ANY    /restapi/employee/{id}
  restapi_employee_list      GET      ANY      ANY    /restapi/employee
  restapi_employee_create    POST     ANY      ANY    /restapi/employee
  restapi_employee_delete    DELETE   ANY      ANY    /restapi/employee/{id}
  restapi_employee_update    PATCH    ANY      ANY    /restapi/employee/{id}
```