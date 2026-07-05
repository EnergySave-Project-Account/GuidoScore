<?php

$files = array_values(array_filter(scandir(__DIR__), function($f){
    return is_file(__DIR__ . '/' . $f);
}));

foreach ($files as $file) {
    if ($file === '_autoloader.php') continue;
    require_once __DIR__ . '/' . $file;
}
