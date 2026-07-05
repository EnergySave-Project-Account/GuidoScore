<?php

spl_autoload_register(function ($class) {

    // Caminho base da pasta core
    $baseDir = __DIR__ . "/";

    // Monta o caminho do arquivo baseado no nome da classe
    $file = $baseDir . $class . ".php";

    // Se existir, carrega
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    $folders = ["services", "database", "security"];

    foreach ($folders as $folder) {
        $file = $baseDir . $folder . "/" . $class . ".php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    $controllerCoreDir = dirname($baseDir) . "/src/core/";
    $controllerFile = $controllerCoreDir . $class . ".php";
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }

});