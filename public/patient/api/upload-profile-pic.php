<?php
session_start();
require_once '../connection/config.php';

header('Content-Type: application/json'); // important for JSON response

if (!isset($_SESSION['email']) || $_SESSION['usertype'] !== 'Patient') {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

$pt_id = $_SESSION['pt_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $uploadDir = '../uploads/profile/';
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    // Make sure directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $relativePath = $targetPath;

        $stmt = $conn->prepare("UPDATE patients SET profile_pic_url = ? WHERE dr_id = ?");
        $stmt->bind_param("si", $relativePath, $pt_id);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Profile picture updated successfully.",
            "imageUrl" => $relativePath
        ]);
        exit();
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Failed to move uploaded file."]);
        exit();
    }
}

http_response_code(400);
echo json_encode(["success" => false, "error" => "Invalid request."]);
?>
