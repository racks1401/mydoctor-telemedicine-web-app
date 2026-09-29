<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/services/admin/UserVerificationService.php";

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
    "verification_status" => $_GET["verification_status"] ?? "pending",
    "status" => $_GET["status"] ?? "",
    "department" => $_GET["department"] ?? "",
    "sort" => $_GET["sort"] ?? "",
    "date_from" => $_GET["date_from"] ?? "",
    "date_to" => $_GET["date_to"] ?? ""
];


try {

    switch ($action) {

        case "table":

            $data = UserVerificationService::getVerificationTableData(
                $conn,
                $page,
                $limit,
                $search,
                $filters
            );
            jsonResponse(true, "Blocked Users Table Data Fetched Successfully", $data);
            break;

        case "stats":
            $data = UserVerificationService::getVerificationStatsData($conn);
            jsonResponse(true, "Blocked Users Stats Data Fetched Successfully", $data);
            break;

        case "approve":
            UserVerificationService::approve($conn);
            break;

        case "reject":
            UserVerificationService::reject($conn);
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