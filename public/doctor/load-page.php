<?php
require_once __DIR__ . '/../../app/middleware/remember-me.php';
require_once __DIR__ . '/../../app/middleware/doctor-auth.php';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'appointments',
    'chats',
    'calls',
    'settings',
    'availability'
];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    exit('Page not found');
}

require_once __DIR__ . '/../../app/views/doctor/' . $page . '.php';