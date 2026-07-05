<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../core/http/Router.php';

if (file_exists(__DIR__ . '/../utils/utils.php')) {
    require_once __DIR__ . '/../utils/utils.php';
}

if (!function_exists('view')) {
    function view($file, $data = []){
        header('Content-Type: text/html; charset=UTF-8');
        extract($data);
        $path = BASE_DIR . '/views/' . $file . '.php';
        if (file_exists($path)) {
            require $path;
            return;
        }
        echo "View not found: $file";
    }
}

$router = new Router();

$routes = require __DIR__ . '/../routes/web.php';
$routes($router);

$route = isset($_GET['route']) ? '/' . $_GET['route'] : str_replace('/src', '', $_SERVER['REQUEST_URI']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $route
);
