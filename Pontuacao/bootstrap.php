<?php

if (defined('BOOTSTRAP_INITIALIZED')) {
    return;
}

define('BOOTSTRAP_INITIALIZED', true);

//----------------------------------------------------
// CARREGANDO OS DIRETÓRIOS DO PROJETO
//--------------------------------------------------

define('BASE_DIR', __DIR__);
define('DONTENV_PATH', BASE_DIR . "/.env");
define('BOOTSTRAP_PATH', BASE_DIR . "/bootstrap.php");

define('COMMON_DIR', BASE_DIR . "/common/core");
define('COMMON_AUTOLOADER_PATH', COMMON_DIR . "/_autoloader.php");

require_once COMMON_AUTOLOADER_PATH;

if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; connect-src 'self' ws://localhost:8000");

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

define('HANDLERS_PATH', BASE_DIR . "/src/core/handlers/");

function requireAuth() {
    if (!isset($_COOKIE["auth_token"]) || !Autentication::authNeeded($_COOKIE["auth_token"])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'Não autorizado'
        ]);
        exit;
    }
}

$fields = Env::get();

define("API_KEY", $fields["API_KEY"] ?? null);

define("DBUSER", $fields["DBUSER"] ?? null);
define("DBPASSWORD", $fields["DBPASSWORD"] ?? null);
define("DBNAME", $fields["DBNAME"] ?? null);
define("DBHOST", $fields["DBHOST"] ?? null);
define("DBPORT", $fields["DBPORT"] ?? 3306);