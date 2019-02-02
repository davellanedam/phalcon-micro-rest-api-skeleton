<?php

$acl = new Phalcon\Acl\Adapter\Memory();

// The default action is DENY access
$acl->setDefaultAction(Phalcon\Acl::DENY);

/*
 * ROLES
 * Superuser - can do anything (Guest, User, and own things)
 * User - can do most things (Guest and own things)
 * Guest - Public
 * */
$acl->addRole(new Phalcon\Acl\Role('Guest'));
$acl->addRole(new Phalcon\Acl\Role('User'));
$acl->addRole(new Phalcon\Acl\Role('Superuser'));

// User can do everything a Guest can do
$acl->addInherit('User', 'Guest');
// Admin can do everything a User can do
$acl->addInherit('Superuser', 'Guest');
$acl->addInherit('Superuser', 'User');

/*
 * RESOURCES
 * for each user, specify the 'controller' and 'method' they have access to ('user_type'=>['controller'=>['method','method','...']],...)
 * this is created in an array as we later loop over this structure to assign users to resources
 * */
$arrResources = [
    'Guest' => [
        'Index' => ['login'],
    ],
    'User' => [
        'Profile' => ['index', 'update', 'changePassword'],
        'Users' => ['index', 'create', 'get', 'search', 'update', 'logout'],
        'Cities' => ['index', 'create', 'get', 'ajax', 'update', 'delete'],
    ],
    'Superuser' => [
        'Users' => ['changePassword', 'delete'],
    ],
];

foreach ($arrResources as $arrResource) {
    foreach ($arrResource as $controller => $arrMethods) {
        $acl->addResource(new Phalcon\Acl\Resource($controller), $arrMethods);
    }
}

/*
 * ACCESS
 * */
foreach ($acl->getRoles() as $objRole) {
    $roleName = $objRole->getName();

    // everyone gets access to global resources
    foreach ($arrResources['Guest'] as $resource => $method) {
        $acl->allow($roleName, $resource, $method);
    }

    // users
    if ($roleName == 'User') {
        foreach ($arrResources['User'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }

    // admins
    if ($roleName == 'Superuser') {
        foreach ($arrResources['Superuser'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }
}
