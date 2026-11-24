<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/services/admin/DoctorsService.php";
require_once __DIR__ . "/../../../app/helpers/auth.php";

// Only logged-in admins may use this API
$adminId = requireRole('admin');

try {

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, (int)($_GET['limit'] ?? 10));
    $search = trim($_GET['search'] ?? '');

    $id = (int)($_POST["id"] ?? 0);
    $filters = [
        "user_type" => $_GET["user_type"] ?? "all",
        "verification_status" => $_GET["verification_status"] ?? "pending",
        "status" => $_GET["status"] ?? "all",
        "department" => $_GET["department"] ?? "",
        "sort" => $_GET["sort"] ?? "",
        "date_from" => $_GET["date_from"] ?? "",
        "date_to" => $_GET["date_to"] ?? ""
    ];

    switch($action){
        case "stats":
            $data = DoctorsService::getDoctorsPageData($conn);
            jsonResponse(
                true,
                "Doctors fetched successfully",
                $data
            );
            break;
        
        case "table":
            $data = DoctorsService::getDoctorsTableData(
                $conn,
                $page,
                $limit,
                $search,
                $filters
            );
            jsonResponse(
                true,
                "Doctors Table Data Fetched Successfully",
                $data
            );
            break;

        case "add":
        case "add_doctor":
            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents("php://input");
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $input = $decoded;
            }
            $input['verified_by'] = $adminId; // the admin adding the doctor
            $result = DoctorsService::addDoctor($conn, $input);
            if (!empty($result['success'])) {
                jsonResponse(true, $result['message'], $result);
            } else {
                http_response_code(400);
                jsonResponse(false, $result['message'] ?? "Failed to add doctor");
            }
            break;
        
        case "viewDoctorInfo":
            break;
        
        default:
            http_response_code(400);
            jsonResponse(false, "Invalid action");
            break;
    }
    
} catch (Exception $e) {

    jsonResponse(
        false,
        $e->getMessage()
    );

}
