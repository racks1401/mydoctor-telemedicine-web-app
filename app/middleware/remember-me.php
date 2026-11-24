<?php
/**
 * Remember-Me auto login
 * If there is no active session but a "remember_token" cookie exists,
 * look up its SHA-256 hash in users.remember_token (the same hash that
 * login_register.php stores) and restore the session for active users only.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {

    require_once __DIR__ . '/../../config/database.php';

    $tokenHash = hash('sha256', (string)$_COOKIE['remember_token']);

    $stmt = $conn->prepare("
        SELECT user_id, name, email, user_type
        FROM users
        WHERE remember_token = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $rememberedUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($rememberedUser) {
        // New session id prevents session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']  = (int)$rememberedUser['user_id'];
        $_SESSION['name']     = $rememberedUser['name'];
        $_SESSION['email']    = $rememberedUser['email'];
        $_SESSION['usertype'] = $rememberedUser['user_type'];
    } else {
        // Invalid, expired or blocked: remove the stale cookie
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
