<?php

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
        'dbname' => 'your_database_schema',
        'charset' => 'utf8',
    ],
    'authentication' => [
        'secret' => 'your secret key to SIGN token', // This will sign the token. (still insecure)
        'encryption_key' => 'Your ultra secret key to ENCRYPT the token', // Secure token with an ultra password
        'expiration_time' => 86400 * 7, // One week till token expires
        'iss' => 'myproject', // Token issuer eg. www.myproject.com
        'aud' => 'myproject', // Token audience eg. www.myproject.com
    ],
];
