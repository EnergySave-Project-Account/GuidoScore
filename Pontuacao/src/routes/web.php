<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../core/services/_autoloader.php';

return function($router) {

    require_once __DIR__ . '/endpoints/_autoloader.php';

    $router->get('/home', function () {
        return view('home');
    });

    $router->get('/tabelas', function () {
        return view('tabelas');
    });

    $router->get('/login', function () {
        return view('login');
    });


};
