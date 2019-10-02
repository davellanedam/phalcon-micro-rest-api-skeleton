Phalcon Micro REST API Basic Project Skeleton
====================

[![Author](http://img.shields.io/badge/author-@davellanedam-blue.svg?style=flat-square)](https://twitter.com/davellanedam)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/davellanedam/phalcon-micro-rest-api-skeleton/blob/master/LICENSE)
[![Release](https://img.shields.io/github/release/davellanedam/phalcon-micro-rest-api-skeleton.svg?style=flat-square)](https://github.com/davellanedam/phalcon-micro-rest-api-skeleton/releases)

Getting started
--------

This is a basic API REST skeleton written on the **ultra hyper mega fastest framework for PHP [phalcon](https://github.com/phalcon/cphalcon)**. A full-stack PHP framework delivered as a C-extension. Its innovative architecture makes Phalcon the fastest PHP framework ever built!

This project is created to help other developers create a **basic REST API in an easy way with phalcon**. This basic example shows how powerful and simple phalcon can be. Do you want to contribute? Pull requests are always welcome to show more features of phalcon.

Features
--------

* JWT Tokens, provide login with `Authorization` header with value `Basic username:password` where `username:password` **MUST BE ENCODED** with `Base64`.
* Make requests with a token after login with `Authorization` header with value `Bearer yourToken` where `yourToken` is the **signed and encrypted token** given in the response from the login process.
* Use ACL so you can have roles for users.
* Timezone ready: Work UTC time (GMT+0). Responses with iso8601 date/time format.
* Pagination ready.
* Filters (JSON).
* Easy deploy to staging and production environments with rsync.
* Internationalization ready. API responses use JSON format to make life easier at the frontend.
* Separate database for logs.
* User profile.
* Users list.
* Cities. (Example of use: call cities API, then send name of the city when creating or updating a user.
* Integrated tests

Requirements
------------

* Apache **2**
* PHP **5.6+**
* Phalcon **3.2+**
* MySQL **5.5+**

How to install
--------------

### Using Git (recommended)

1. First you need to [install composer](https://getcomposer.org/download/) if you haven´t already.
2. Clone the project from github. Change 'myproject' to you project name.
```bash
git clone https://github.com/davellanedam/phalcon-micro-api.git ./myproject
```

### Using manual download ZIP

1. Download repository
2. Uncompress to your desired directory

### Install composer dependencies after installing (Git or manual download)

```bash
cd myproject
composer install
composer update
```
### Database Configuration and Security

There are 3 files in the `/myproject/config` directory, (development, staging and production) each one is meant to be used on different environments to make your life easier on deployment.

1. Create a MySQL database with your custom name and then import `myproject.sql` (in the `/schemas` directory)
2. Create a second MySQL database with your custom name and then import `myproject_log.sql` (in the `/schemas` directory).
3. Open `/myproject/config/server.development.php` and setup your DEVELOPMENT (local) database connection credentials
4. Open `/myproject/config/server.staging.php` and setup your STAGING (testing server) database connection credentials
5. Open `/myproject/config/server.production.php` and setup your PRODUCTION (production server) database connection credentials

This is the structure of those 3 files, remember to change values for yours.
```php
return [
    'database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => 'your_ip_or_hostname',
        'username' => 'your_db_username',
        'password' => 'your_db_password',
        'dbname' => 'your_database_schema',
        'charset' => 'utf8',
    ],
    'log_database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => 'your_ip_or_hostname',
        'username' => 'your_db_username',
        'password' => 'your_db_password',
        'dbname' => 'myproject_log',
        'charset' => 'utf8',
    ],
    'authentication' => [
        'secret' => 'your secret key to SIGN token', // This will sign the token. (still insecure)
        'encryption_key' => 'Your ultra secret key to ENCRYPT the token', // Secure token with an ultra password
        'expiration_time' => 86400 * 7, // One week till token expires
        'iss' => 'myproject', // Token issuer eg. www.myproject.com
        'aud' => 'myproject', // Token audience eg. www.myproject.com
    ]
];
```

### Setting up environments
The ENV variable is set on an .htaccess file located at `/public/.htaccess` that you must upload **once** to each server you use. Change the environment variable on each server to what you need. To make your life easy this .htaccess file is on the **excluded files list to upload** when you make a deploy. Possible values are: `development`, `staging` and `production`.
```bash
############################################################
# Possible values: development, staging, production        #
# Change value and upload ONCE to your server              #
# AVOID re-uploading when deployment, things will go crazy #
############################################################
SetEnv APPLICATION_ENV "development"
```

Usage
--------------

Once everything is set up to test API routes either use Postman or any other api testing application. Remember to change the URL of the **provided example Postman JSON file**. Default username/password combination for login is `admin/admin1234`.

If you use Postman please go to `manage environments` and then create one for each of your servers. Create a new key `authToken` with `token` value (the token you got from the login process), each time you make a request to the API it will send `Authorization` header with the token value in the request, you can check this on the headers of users or cities endpoints in the Postman example.

This is a REST API, so it works using the following HTTP methods:

* GET (Read): Gets a list of items, or a single item
* POST (Create): Creates an item
* PATCH (Update): Updates an item
* DELETE: Deletes an item

### Testing
There are some tests included, to run tests you need to go to the command line and type:
```bash
composer test
```

### Creating new models
If you need to add more models to the project there´s an easy way to do it with `phalcondevtools` (If you did `composer install`, you already have this).
Step into a terminal window and open your project folder, then type the following and you are set!
```bash
phalcon model --name=your_table_name --schema=your_database --mapcolumn
```

### Creating new controllers
If you need to add more controllers to the project there´s an easy way to do it with `phalcondevtools` (If you did `composer install`, you already have this).
Step into a terminal window and open your project folder, then type the following.
```bash
phalcon controller --name=your_controller_name_without_the_controller_word
```
When it´s done, it creates your new controller, but if you want to use `ControllerBase.php` functions in your newly created controller you must change the following line in the new controller:
```php
class MyNewController extends \Phalcon\Mvc\Controller
```
to this:
```php
class MyNewController extends ControllerBase
```

### Creating new routes
You can add more routes to your project by adding them into the `/app.php` file. This is an example of `/users` routes:
```php
/**
* Users
*/
$users = new MicroCollection();
$users->setHandler('UsersController', true);
$users->setPrefix('/users');
// Gets all users
$users->get('/', 'index');
// Creates a new user
$users->post('/create', 'create');
// Gets user based on unique key
$users->get('/get/{id}', 'get');
// Updates user based on unique key
$users->patch('/update/{id}', 'update');
// Changes user password
$users->patch('/change-password/{id}', 'changePassword');
// Adds users routes to $app
$app->mount($users);
```

Remember to add the controller (without the controller word) and methods of endpoints to the `/config/acl.php`file. Otherwise you will get this response from the API: `'common.YOUR_USER_ROLE_DOES_NOT_HAVE_THIS_FEATURE',`
```php
/*
 * RESOURCES
 * for each user, specify the 'controller' and 'method' they have access to ('user_type'=>['controller'=>['method','method','...']],...)
 * */
$arrResources = [
    'Guest'=>[
        'Users'=>['login'],
    ],
    'User'=>[
        'Profile'=>['index','update','changePassword'],
        'Users'=>['index','create','get','search','update','logout'],
        'Cities'=>['index','create','get','ajax','update','delete'],
    ],
    'Superuser'=>[
        'Users'=>['changePassword'],
    ]
];
```

Always keep in mind the following:
```php
/*
 * ROLES
 * Superuser - can do anything (Guest, User, and own things)
 * User - can do most things (Guest and own things)
 * Guest - Public
 * */
 ```

Internationalization
-------
API is designed to response with a JSON, so at the FRONTEND you can use lang files like this:
Put your language files in 'langs' directory at frontend:

* `langs`
    * `en.json`
    * `es.json`

Example of language file `en.json` :

```json
{
    "common" : {
        "HEADER_AUTHORIZATION_NOT_SENT": "Header Authorization not sent",
        "EMPTY_TOKEN_OR_NOT_RECEIVED": "Empty token or not received",
        "YOUR_USER_ROLE_DOES_NOT_HAVE_THIS_FEATURE": "Your user role does not have this feature",
        "BAD_TOKEN_GET_A_NEW_ONE": "Bad token, get a new one",
        "SUCCESSFUL_REQUEST": "Succesfull request",
        "CREATED_SUCCESSFULLY": "Created successfully",
        "THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME": "There is already a record with that name",
        "UPDATED_SUCCESSFULLY": "Updated successfully",
        "DELETED_SUCCESSFULLY": "Deleted successfully",
        "THERE_HAS_BEEN_AN_ERROR": "There has been an error",
        "INCOMPLETE_DATA_RECEIVED": "Incomplete data received",
        "NO_RECORDS": "No records",
        "COULD_NOT_BE_CREATED": "Could not be created",
        "NOT_FOUND": "Not found",
        "COULD_NOT_BE_DELETED": "Could not be deleted",
        "COULD_NOT_BE_UPDATED": "Could not be updated"
    },
    "login" : {
        "USER_IS_NOT_REGISTERED": "User is not registered",
        "USER_BLOCKED": "User blocked",
        "USER_UNAUTHORIZED": "User unauthorized",
        "WRONG_USER_PASSWORD": "Wrong user password",
        "TOO_MANY_FAILED_LOGIN_ATTEMPTS": "Too many failed login attempts"
    },
    "profile" : {
        "PROFILE_NOT_FOUND": "Profile not found",
        "PROFILE_UPDATED": "Profile updated",
        "PROFILE_COULD_NOT_BE_UPDATED": "Profile could not be updated",
        "ANOTHER_USER_ALREADY_REGISTERED_WITH_THIS_USERNAME": "Another user already registered with this username"
    },
    "change-password" : {
        "PASSWORD_COULD_NOT_BE_UPDATED": "Password could not be updated",
        "PASSWORD_SUCCESSFULLY_UPDATED": "Password updated successfully",
        "WRONG_CURRENT_PASSWORD": "Wrong current password"
    }
}
```

Example of language file `es.json` :

```json
{
    "common": {
        "HEADER_AUTHORIZATION_NOT_SENT": "Autorización de encabezado no enviada",
        "EMPTY_TOKEN_OR_NOT_RECEIVED": "Token vacío o no recibido",
        "YOUR_USER_ROLE_DOES_NOT_HAVE_THIS_FEATURE": "Su rol de usuario no tiene esta característica",
        "BAD_TOKEN_GET_A_NEW_ONE": "Token incorrecto, solicite uno nuevo",
        "SUCCESSFUL_REQUEST": "Solicitud exitosa",
        "THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME": "Ya existe un registro con ese nombre",
        "THERE_HAS_BEEN_AN_ERROR": "Ocurrió un error",
        "INCOMPLETE_DATA_RECEIVED": "Los datos recibidos están incompletos",
        "NO_RECORDS": "No hay registros",
        "NOT_FOUND": "No encontrado",
        "CREATED_SUCCESSFULLY": "Creado con éxito",
        "DELETED_SUCCESSFULLY": "Eliminado con éxito",
        "UPDATED_SUCCESSFULLY": "Actualizado con éxito",
        "COULD_NOT_BE_CREATED": "No se pudo crear",
        "COULD_NOT_BE_DELETED": "No se pudo eliminar",
        "COULD_NOT_BE_UPDATED": "No se pudo actualizar"
    },
    "login": {
        "USER_IS_NOT_REGISTERED": "El usuario no está registrado",
        "USER_BLOCKED": "Usuario bloqueado",
        "USER_UNAUTHORIZED": "Usuario no autorizado",
        "WRONG_USER_PASSWORD": "Contraseña de usuario incorrecta",
        "TOO_MANY_FAILED_LOGIN_ATTEMPTS": "Demasiados intentos fallidos de inicio de sesión"
    },
    "profile": {
        "PROFILE_NOT_FOUND": "Perfil no encontrado",
        "PROFILE_UPDATED": "Perfil actualizado correctamente",
        "PROFILE_COULD_NOT_BE_UPDATED": "No se pudo actualizar el perfil",
        "ANOTHER_USER_ALREADY_REGISTERED_WITH_THIS_USERNAME": "Otro usuario ya se ha registrado con este nombre de usuario"
    },
    "change-password": {
        "PASSWORD_COULD_NOT_BE_UPDATED": "No se pudo actualizar la contraseña",
        "PASSWORD_SUCCESSFULLY_UPDATED": "Contraseña actualizada exitosamente",
        "WRONG_CURRENT_PASSWORD": "La contraseña actual es incorrecta"
    }
}
```

Deployment
-------
You can make a deploy to staging or production servers. It will run a bash file with `rsync` command to sync your project directory to the servers project directory. Step into a terminal window and open your project folder, then type the following.

### Deploy to staging server
```bash
deploy-staging.sh
```

### Deploy to production server
```bash
deploy-production.sh
```
### Do not forget to change variables to yours on each .sh file
```bash
PROJECT=myproject
USER=server_username
URL=my_staging_server_url
```

### Excluding files on deploy
You can exclude files on each deployment environment (`/exclude-production.txt` and `/exclude-staging.txt`), add files you want to be excluded on the corresponding .txt file. Each excluded file or directory must be on a new line. Staging exclusion list example:

```txt
.git/
*.DS_Store
.phalcon/
.php/
cache/
.htaccess
exclude-staging.txt
exclude-production.txt
deploy-staging.sh
deploy-production.sh
config/server.development.php
config/server.production.php
public/.htaccess
schemas/
```

Bugs or improvements
-------
Feel free to report any bugs or improvements. Pull requests are always welcome.

I love this! How can I help?
-------
It´s amazing you feel like that! Send me a tweet https://twitter.com/davellanedam, share this with others, make a pull request or if you feel really thankful you can always buy me a beer! Enjoy!

License
-------

This project is open-sourced software licensed under the MIT License. See the LICENSE file for more information.
