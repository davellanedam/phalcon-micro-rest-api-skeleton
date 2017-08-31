<?php

return [
    'database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 'mysql',
        'dbname' => 'myproject',
        'charset' => 'utf8',
    ],
    'authentication' => [
        'secret' => 'your secret key to SIGN token', // This will sign the token. (still insecure)
        'encryption_key' => 'Your ultra secret key to ENCRYPT the token', // Secure token with an ultra password
        'expirationTime' => 86400 * 7, // One week till token expires
    ]
];
