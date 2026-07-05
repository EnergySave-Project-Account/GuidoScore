<?php
// Router para PHP built-in server
$file = __DIR__ . '/../public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file($file) || is_dir($file)) {
    return false; // Serve arquivo/diretório
}
require __DIR__ . '/../public/index.php';
