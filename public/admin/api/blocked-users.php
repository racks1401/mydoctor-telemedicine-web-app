<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/services/admin/BlockedUserService.php";

if (!$conn) {
    http_response_code(500);
    jsonResponse(false, "Database connection failed");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(1, (int)($_GET['limit'] ?? 10));
$search = trim($_GET['search'] ?? '');

$id = (int)($_POST["id"] ?? 0);
$filters = [
    "user_type" => $_GET["user_type"] ?? "all",
    "status" => $_GET["status"] ?? "",
    "department" => $_GET["department"] ?? "",
    "sort" => $_GET["sort"] ?? "",
    "date_from" => $_GET["date_from"] ?? "",
    "date_to" => $_GET["date_to"] ?? ""
];

try {

    switch ($action) {

        case "table":
            $data = BlockedUserService::getBlockedTableData(
                $conn,
                $page,
                $limit,
                $search,
                $filters
            );
            jsonResponse(true, "Blocked Users Table Data Fetched Successfully", $data);
            break;

        case "stats":
            $data = BlockedUserService::getBlockedStatsData($conn);
            jsonResponse(true, "Stats Data Fetched Successfully", $data);
            break;

        case "unblock":
            $success = BlockedUserService::unblockUser($conn, $id);

            if ($success) {
                jsonResponse(true, "User unblocked successfully.");
            } else {
                jsonResponse(false, "Failed to unblock user.");
            }

            break;

        default:
            http_response_code(400);
            jsonResponse(false, "Invalid action");
            break;
    }
 

    

} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, $e->getMessage());
}