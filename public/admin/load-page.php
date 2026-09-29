<?php
require_once __DIR__ . '/../../app/middleware/remember-me.php';
require_once __DIR__ . '/../../app/middleware/admin-auth.php';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'doctors',
    'patients',
    'appointments',
    'user-verification',
    'blocked-users',
    'profile'
];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    exit('Page not found');
}

require_once __DIR__ . '/../../app/views/admin/' . $page . '.php';