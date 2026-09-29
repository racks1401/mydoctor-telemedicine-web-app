<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$email = $_SESSION['email'] ?? null;

if (!$email) {
    $userData = [
        'name' => 'Admin',
        'phone' => '',
        'profile_url' => null
    ];
    return;
}

$stmt = $conn->prepare(
    "SELECT name, phone, profile_url FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

$userData = $result->num_rows === 1
    ? $result->fetch_assoc()
    : [
        'name' => 'Admin',
        'phone' => '',
        'profile_url' => null
    ];