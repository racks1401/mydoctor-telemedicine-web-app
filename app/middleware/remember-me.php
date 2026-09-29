<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['email']) && isset($_COOKIE['remember_token'])) {

    require_once __DIR__ . '/../../config/database.php';

    $token = $_COOKIE['remember_token'];

    // Check in ALL user tables
    $queries = [
        'admin'   => "SELECT email, name FROM admins WHERE remember_token = ?",
        'doctor'  => "SELECT email, name FROM doctors WHERE remember_token = ?",
        'patient' => "SELECT email, name FROM patients WHERE remember_token = ?",
    ];

    foreach ($queries as $role => $sql) {

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Restore session
            $_SESSION['email']    = $user['email'];
            $_SESSION['name']     = $user['name'];
            $_SESSION['usertype'] = $role;

            break;
        }
    }
}