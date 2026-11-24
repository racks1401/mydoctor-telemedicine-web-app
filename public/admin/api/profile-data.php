<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/models/UserModel.php";

try {
    $userId = $_SESSION['user_id'] ?? null;
    $email = $_SESSION['email'] ?? null;

    $userRole = strtolower($_SESSION['usertype'] ?? '');
    if ((!$userId && !$email) || $userRole !== 'admin') {
        jsonResponse(
            false,
            "Unauthorized access"
        );
    }

    if ($userId) {
        $data = UserModel::getUserById($conn, (int)$userId);
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, email, profile_url, user_type, theme FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc() ?? [];
    }

    if (empty($data)) {
        jsonResponse(
            false,
            "Admin not found"
        );
    }

    jsonResponse(
        true,
        "Profile data fetched successfully",
        $data
    );

} catch (Exception $e) {
    jsonResponse(
        false,
        $e->getMessage()
    );
}
