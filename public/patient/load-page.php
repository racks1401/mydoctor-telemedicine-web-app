<?php
require_once __DIR__ . '/../../app/middleware/remember-me.php';
require_once __DIR__ . '/../../app/middleware/patient-auth.php';

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard',
    'appointments',
    'doctors',
    'calls',
    'chats',
    'settings'
];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    exit('Page not found');
}

require_once __DIR__ . '/../../app/views/patient/' . $page . '.php';