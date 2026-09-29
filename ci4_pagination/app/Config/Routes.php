<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::entry');
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login', ['filter' => 'csrf']);
$routes->get('setup', 'Auth::setupForm');
$routes->post('setup', 'Auth::setup', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);

$routes->get('dashboard', 'Home::index', ['filter' => 'auth']);
$routes->get('account/(:num)', 'Home::viewAccount/$1', ['filter' => 'auth']);
