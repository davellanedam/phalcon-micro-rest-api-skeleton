<?php

date_default_timezone_set('UTC');

use Phalcon\Mvc\Micro;
use Phalcon\Events\Manager as EventsManager;

define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'development');

if (APPLICATION_ENV === 'development') {
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
    $debug = new Phalcon\Debug();
    $debug->listen();
}

define('APP_PATH', realpath('..'));

try {

    require __DIR__ . '/../vendor/autoload.php';

    /*
     * Read the configuration
     */
    $config = include __DIR__ . '/../config/config.php';

    /**
     * Include Autoloader.
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Include Services.
     */
    include APP_PATH . '/config/services.php';

    /**
     * Include ACL.
     */
    include APP_PATH . '/config/acl.php';

    /*
     * Starting the application
     * Assign service locator to the application
     */
    $app = new Micro($di);

    /**
     * Include Application.
     */
    include APP_PATH . '/app.php';

    /*
     * Handle the request
     */
    $app->handle();

} catch (\Exception $e) {
    if (APPLICATION_ENV === 'development') {
        print_r($e->getMessage() . '<br>');
        print_r('<pre>' . $e->getTraceAsString() . '</pre>');
    }
}
