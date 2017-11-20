<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */

use Phalcon\Mvc\View\Simple as View;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Crypt;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Firebase\JWT\JWT as JWT;

$di = new FactoryDefault();

/**
 * Models manager
 */
$di->set('modelsManager', function () {
    $modelsManager = new ModelsManager();
    return $modelsManager;
});

/**
 * Sets the view component
 */
$di->setShared('view', function () use ($config) {
    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    return $view;
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
});

/**
 * Crypt service
 */
$di->set('mycrypt', function () use ($config) {
    $crypt = new Crypt();
    $crypt->setKey($config->get('authentication')->encryption_key);
    return $crypt;
}, true);

/**
 * JWT service
 */
$di->setShared('jwt', function () {
    return new JWT();
});

/**
 * tokenConfig
 */
$di->setShared('tokenConfig', function () use ($config) {
    $tokenConfig = $config->authentication->toArray();
    return $tokenConfig;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use ($config) {
    $dbConfig = $config->database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    $connection = new $class($dbConfig);
    $connection->setNestedTransactionsWithSavepoints(true);

    return $connection;
});

/**
 * Another Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db_log', function () use ($config) {
    $dbConfig = $config->log_database->toArray();
    $adapter = $dbConfig['adapter'];
    unset($dbConfig['adapter']);

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

    $connection = new $class($dbConfig);
    $connection->setNestedTransactionsWithSavepoints(true);

    return $connection;
});
