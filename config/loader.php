<?php

/**
 * Registering an autoloader
 */
$loader = new \Phalcon\Loader();

$loader->registerDirs(
    array(
        $config->application->controllersDir,
        $config->application->middlewaresDir,
        $config->application->modelsDir,
        $config->application->libraryDir,
    )
)->register();
