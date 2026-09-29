<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {

    $stmt = $conn->prepare("
        UPDATE users 
        SET remember_token = NULL 
        WHERE user_id = ?
    ");

    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Remove remember_token cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie(
        "remember_token",
        "",
        time() - 3600,
        "/",
        "",
        false,
        true
    );
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: /my_doctor/public/");
exit;