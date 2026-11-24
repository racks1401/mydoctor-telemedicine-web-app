<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../config/firebase.php';

$userId = me();
if (!$userId) {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'Unauthorized access. Please login first.'
    ]);
    exit;
}

$token = createFirebaseCustomToken($userId);
if (!$token) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Failed to generate Firebase authentication token.'
    ]);
    exit;
}

echo json_encode([
    'status' => true,
    'token' => $token,
    'userId' => (string)$userId,
    'projectId' => 'my-doctor-a20e8',
    'databaseURL' => 'https://my-doctor-a20e8-default-rtdb.firebaseio.com',
    'apiKey' => 'AIzaSyDb_muPLKkmiRmSiq_99HZENYR3FLbHAJg',
    'authDomain' => 'my-doctor-a20e8.firebaseapp.com',
    'appId' => '1:578457659049:web:b092ef3478768fa5736bb8',
    'messagingSenderId' => '578457659049'
]);
exit;
