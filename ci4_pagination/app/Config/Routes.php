<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::entry');
$routes->get('login', 'Auth::loginForm');

$routes->get('dashboard', 'Home::index');
$routes->get('account/(:num)', 'Home::viewAccount/$1');
