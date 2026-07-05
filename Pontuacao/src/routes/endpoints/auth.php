<?php

$router->post('/login', function () {
    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode(AuthService::Login($data));
}, ['rateLimit' => ['maxAttempts' => 5, 'windowSeconds' => 300, 'banSeconds' => 600]]);