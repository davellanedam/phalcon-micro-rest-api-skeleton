Phalcon Micro REST API
====================

[![Author](http://img.shields.io/badge/author-@davellanedam-blue.svg?style=flat-square)](https://twitter.com/davellanedam)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/davellanedam/phalcon-micro-api/blob/master/LICENSE)

Getting started
--------

This is a basic API REST written on [phalconPHP framework](https://github.com/phalcon/cphalcon).
This project is created to help developers create a REST API in an easy way.

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
2. Clone the project from github. Change "myproject" to you project name.
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
### Database Configuration

There are 3 files in the `/myproject/config` directory, (development, staging and production) each one is meant to be used on different environments to make your life easier on deployment.

1. Create a MySQL database with your custom name and then import `myproject.sql` (in the `/schemas` directory)
2. Open `/myproject/config/server.development.php` and setup your DEVELOPMENT (local) database connection credentials
3. Open `/myproject/config/server.staging.php` and setup your STAGING (testing server) database connection credentials
4. Open `/myproject/config/server.production.php` and setup your PRODUCTION (production server) database connection credentials

This is the structure of those files, remember to change values for yours.
```php
return [
    'database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => 'your_ip_or_hostname',
        'username' => 'your_db_username',
        'password' => 'your_db_password',
        'dbname' => 'your_database_schema',
        'charset' => 'utf8',
    ]
];
```

### Security

**WARNING:** This software uses JWT Tokens for every request. This token is also encrypted.

You **MUST** change default keys for **TOKEN** on each of your environments (development, staging, production) so each one has its own key. Go to `/myproject/config/config.php` and change the values.
```php
'authentication' => [
    'secret' => '8j96ycslFmhBeqtQi9qC9c8ri2aYmwRiptUDOQ16', // 40 chars CHANGE THIS FOR EVERY PROJECT
    'expirationTime' => 86400 * 7, // One week till token expires
]
```

You **MUST** change default keys for **ENCRYPTION OF TOKEN** on each of your environments (development, staging, production) so each one has its own key. Go to `/myproject/config/services.php` and change the values.
```php
/**
 * Crypt service
 */
$di->set('mycrypt', function () {
    $crypt = new Crypt();
    // Set a global encryption key CHANGE THIS FOR EVERY PROJECT (16 chars)!
    $crypt->setKey('#D^M68arBtC1O$V6');
    return $crypt;
}, true);
```

Usage
--------------

Once everything is set up to test API routes either use Postman or any other api testing application. Remember to change the URL of the provided example Postman JSON file.

This is a REST API, so it works using the following HTTP methods:

* GET (Read): Gets a list of items, or a single item
* POST (Create): Creates an item
* PATCH (Update): Updates an item
* DELETE: Deletes an item

License
-------

This project is open-sourced software licensed under the MIT License. See the LICENSE file for more information.
