<?php

$file = __DIR__ . $_SERVER['REQUEST_URI'];

if (is_file($file)) {
    return false;
}

require __DIR__ . '/../src/controller/controller.php';
