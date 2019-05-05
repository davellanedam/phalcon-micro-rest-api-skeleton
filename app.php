<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

/**
 * ACL checks
 */
$app->before(new AccessMiddleware());

/**
 * Insert your Routes below
 */

/**
 * Index
 */
$index = new MicroCollection();
$index->setHandler('IndexController', true);
// Gets index
$index->get('/', 'index');
// Authenticates a user
$index->post('/authenticate', 'login');
// logout
$index->get('/logout', 'logout');
// Adds index routes to $app
$app->mount($index);

/**
 * Profile
 */
$profile = new MicroCollection();
$profile->setHandler('ProfileController', true);
$profile->setPrefix('/profile');
// Gets profile
$profile->get('/', 'index');
// // Updates user profile
$profile->patch('/update', 'update');
// Changes user password
$profile->patch('/change-password', 'changePassword');
// Adds profile routes to $app
$app->mount($profile);

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
// Deletes user based on unique key
$users->delete('/delete/{id}', 'delete');
// Adds users routes to $app
$app->mount($users);

/**
 * Cities
 */
$cities = new MicroCollection();
$cities->setHandler('CitiesController', true);
$cities->setPrefix('/cities');
// Gets cities
$cities->get('/', 'index');
// Creates a new city
$cities->post('/create', 'create');
// Gets city based on unique key
$cities->get('/get/{id}', 'get');
// Updates city based on unique key
$cities->patch('/update/{id}', 'update');
// Deletes city based on unique key
$cities->delete('/delete/{id}', 'delete');
// Adds cities routes to $app
$app->mount($cities);

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, 'Not Found')->sendHeaders();
    $app->response->setContentType('application/json', 'UTF-8');
    $app->response->setJsonContent(array(
        'status' => 'error',
        'code' => '404',
        'messages' => 'URL Not found',
    ));
    $app->response->send();
});

/**
 * Error handler
 */
$app->error(
    function ($exception) {
        print_r('An error has occurred');
    }
);
