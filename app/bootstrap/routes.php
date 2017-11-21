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
$router->add('/:controller/:action/:params', ['controller' => 1, 'action' => 2, 'params' => 3]);
$router->add('/:controller', ['controller' => 1]);


// RESTFUL API
$router->add('/(v[0-9]+)/:controller', ['module' => 1, 'controller' => 2, 'do' => 'get'])->via(['GET']);
$router->add('/(v[0-9]+)/:controller', ['module' => 1, 'controller' => 2, 'do' => 'create'])->via(['POST']);
$router->add('/(v[0-9]+)/:controller', ['module' => 1, 'controller' => 2, 'do' => 'update'])->via(['PUT']);
$router->add('/(v[0-9]+)/:controller', ['module' => 1, 'controller' => 2, 'do' => 'delete'])->via(['DELETE']);

$router->add('/(v[0-9]+)/:controller/:action', ['module' => 1, 'controller' => 2, 'action' => 3, 'do' => 'get'])->via(['GET']);
$router->add('/(v[0-9]+)/:controller/:action', ['module' => 1, 'controller' => 2, 'action' => 3, 'do' => 'create'])->via(['POST']);
$router->add('/(v[0-9]+)/:controller/:action', ['module' => 1, 'controller' => 2, 'action' => 3, 'do' => 'update'])->via(['PUT']);
$router->add('/(v[0-9]+)/:controller/:action', ['module' => 1, 'controller' => 2, 'action' => 3, 'do' => 'delete'])->via(['DELETE']);

$router->add('/(v[0-9]+)/:controller/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'argv' => 3, 'do' => 'get'])->via(['GET']);
$router->add('/(v[0-9]+)/:controller/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'argv' => 3, 'do' => 'create'])->via(['POST']);
$router->add('/(v[0-9]+)/:controller/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'argv' => 3, 'do' => 'update'])->via(['PUT']);
$router->add('/(v[0-9]+)/:controller/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'argv' => 3, 'do' => 'delete'])->via(['DELETE']);

$router->add('/(v[0-9]+)/:controller/:action/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'action' => 3, 'argv' => 4, 'do' => 'get'])->via(['GET']);
$router->add('/(v[0-9]+)/:controller/:action/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'action' => 3, 'argv' => 4, 'do' => 'create'])->via(['POST']);
$router->add('/(v[0-9]+)/:controller/:action/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'action' => 3, 'argv' => 4, 'do' => 'update'])->via(['PUT']);
$router->add('/(v[0-9]+)/:controller/:action/([a-z0-9]{24})', ['module' => 1, 'controller' => 2, 'action' => 3, 'argv' => 4, 'do' => 'delete'])->via(['DELETE']);


$router->setDefaultModule('v1');


return $router;