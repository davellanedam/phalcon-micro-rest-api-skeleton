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
    'authentication' => [
        'secret' => 'your secret key to sign token', // This does not mean it´s encrypted, just signed. (insecure)
        'encryption_key' => 'Your ultra secret key to encrypt the token', // Now we make our token secure with an ultra password
        'expirationTime' => 86400 * 7, // One week till token expires
    ]
];
