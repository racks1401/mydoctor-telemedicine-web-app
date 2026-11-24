<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/services/NotificationService.php";

try {
    $userId = $_SESSION['user_id'] ?? 0;
    $userType = strtolower($_SESSION['usertype'] ?? '');

    if (!$userId || $userType !== 'admin') {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access");
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $jsonData = [];
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $jsonData = $decoded;
        }
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? $jsonData['action'] ?? 'list';

    switch ($action) {
        case "list":
            $limit = max(1, min(100, (int)($_GET['limit'] ?? $jsonData['limit'] ?? 50)));
            $data = NotificationService::getUserNotifications($conn, $userId, $limit);
            jsonResponse(true, "Notifications fetched successfully", $data);
            break;

        case "unread_count":
            $count = NotificationService::getUnreadCount($conn, $userId);
            jsonResponse(true, "Unread count fetched", ['unread_count' => $count]);
            break;

        case "mark_all_read":
            NotificationService::markAllAsRead($conn, $userId);
            jsonResponse(true, "All notifications marked as read");
            break;

        case "mark_read":
            $notifId = (int)($jsonData['notification_id'] ?? $_POST['notification_id'] ?? 0);
            if ($notifId > 0) {
                NotificationService::markAsRead($conn, $notifId, $userId);
            }
            jsonResponse(true, "Notification marked as read");
            break;

        default:
            http_response_code(400);
            jsonResponse(false, "Invalid action");
            break;
    }

} catch (Throwable $e) {
    http_response_code(400);
    jsonResponse(false, $e->getMessage());
}
