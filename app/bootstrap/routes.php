<?php

/**
 * @name    routes .php
 * @author  joe@xxtime.com
 * @link    https://docs.phalconphp.com/zh/3.2/routing
 *
 * Not Found
 * $router->notFound([
 *     'controller' => 'public',
 *     'action'     => 'show404',
 * ]);
 *
 */
use Phalcon\Mvc\Router;


$router = new Router(false);
$router->removeExtraSlashes(true);


// 通用路由
$router->add(
    '/:controller/:action/:params',
    [
        'controller' => 1,
        'action'     => 2,
        'params'     => 3
    ]
);

$router->add(
    '/:controller',
    [
        'controller' => 1
    ]
);


// RESTFUL API
$router->add(
    '/(v[0-9]+)/:controller/:params',
    [
        'module'     => 1,
        'controller' => 2,
        'action'     => 'index',
        'params'     => 3,
    ]
)->via(['GET']);

$router->add(
    '/(v[0-9]+)/:controller/:params',
    [
        'module'     => 1,
        'controller' => 2,
        'action'     => 'create',
        'params'     => 3,
    ]
)->via(['POST']);

$router->add(
    '/(v[0-9]+)/:controller/:params',
    [
        'module'     => 1,
        'controller' => 2,
        'action'     => 'update',
        'params'     => 3,
    ]
)->via(['PUT']);

$router->add(
    '/(v[0-9]+)/:controller/:params',
    [
        'module'     => 1,
        'controller' => 2,
        'action'     => 'delete',
        'params'     => 3,
    ]
)->via(['DELETE']);


$router->setDefaultModule('v1');


return $router;