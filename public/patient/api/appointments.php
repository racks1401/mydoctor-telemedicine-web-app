<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/patient/AppointmentService.php";

try{

    if (!isset($_SESSION['user_id'])){
        jsonResponse(
            false,
            "Unauthorized access"
        );
    }

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
        
        case "table":
            $data = AppointmentService::getPatientsAppointmentsTableData(
                $conn,
                $page,
                $limit,
                $_SESSION['user_id'],
                $search,
                $filters
                );

            jsonResponse(
                true,
                "Appointment Data Fetched Successfully",
                $data
            );
            break;
        
        case "viewAppointments":
            
            break;
        
        default:
            http_response_code(400);
            jsonResponse(false, "Invalid action");
            break;
    }


    

}catch(Exception $e){
    jsonResponse(
        false,
        $e->getMessage()
    );
}