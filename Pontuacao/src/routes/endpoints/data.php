<?php

$router->get('/refresh-once', function () {
    echo json_encode(DataService::refreshTableData());
}, ['rateLimit' => ['maxAttempts' => 30, 'windowSeconds' => 60, 'banSeconds' => 60]]);

$router->post('/increment-points', function () {
    requireAuth();

    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode(DataService::IncrementPoints($data));
}, ['rateLimit' => ['maxAttempts' => 5, 'windowSeconds' => 60, 'banSeconds' => 300]]);

$router->post('/update-points', function () {
    requireAuth();

    $data = json_decode(file_get_contents('php://input'), true);

    echo json_encode(DataService::UpdatePoints($data));
}, ['rateLimit' => ['maxAttempts' => 5, 'windowSeconds' => 60, 'banSeconds' => 300]]);