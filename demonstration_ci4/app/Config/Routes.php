<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/about', 'About::index');
$routes->get('/services', 'Services::index');
$routes->match(['get', 'post'], '/contact', 'Contact::index');
$routes->get('/register', 'Register::index'); //rerouted to page
$routes->post('/register', 'Register::create'); //when creating, inserting; points to database
$routes->get('/login', 'Login::index');
$routes->post('/login', 'Login::authenticate');
$routes->post('/logout', 'Login::logout');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/account/new', 'Dashboard::newAccount');
$routes->post('/account', 'Dashboard::createAccount');
$routes->get('/account/(:num)', 'Dashboard::viewAccount/$1');
$routes->get('/account/(:num)/edit', 'Dashboard::editAccount/$1');
$routes->post('/account/(:num)', 'Dashboard::updateAccount/$1');
$routes->post('/account/(:num)/delete', 'Dashboard::deleteAccount/$1');


