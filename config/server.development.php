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
        'secret' => 'your secret key to sign token', // This does not mean it´s encrypted, just signed. (insecure)
        'encryption_key' => 'Your ultra secret key to encrypt the token', // Now we make our token secure with an ultra password
        'expirationTime' => 86400 * 7, // One week till token expires
    ]
];
