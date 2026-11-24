<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/patient/DoctorsService.php";

try{

    if (!isset($_SESSION['user_id'])){
        jsonResponse(
            false,
            "Unauthorized access"
        );
    }

    $currentUserId = (int)$_SESSION['user_id'];
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, (int)($_GET['limit'] ?? 10));
    $search = trim($_GET['search'] ?? '');

    $id = (int)($_POST["id"] ?? 0);
    $filters = [
        "user_type" => $_GET["user_type"] ?? "all",
        "availability" => $_GET["availability"] ?? "all",
        "verification_status" => $_GET["verification_status"] ?? "pending",
        "status" => $_GET["status"] ?? "all",
        "department" => $_GET["department"] ?? "",
        "sort" => $_GET["sort"] ?? "",
        "date_from" => $_GET["date_from"] ?? "",
        "date_to" => $_GET["date_to"] ?? ""
    ];

    switch($action){
        
        case "table":
            $data = DoctorsService::getPatientsDoctorsTableData(
                $conn,
                $page,
                $limit,
                $search,
                $filters
            );

            jsonResponse(
                true,
                "Doctors Data Fetched Successfully",
                $data
            );
            break;
        
        case "getDoctorBookingInfo":
            $doctorId = (int)($_GET['doctor_id'] ?? $_POST['doctor_id'] ?? 0);
            if ($doctorId <= 0) {
                jsonResponse(false, "Doctor ID is required");
            }

            $doctorInfo = DoctorsService::getDoctorBookingInfo($conn, $doctorId);
            if (!$doctorInfo) {
                jsonResponse(false, "Doctor not found or inactive");
            }

            // Fetch current patient details for auto-fill in Step 2
            $stmtPat = $conn->prepare("SELECT name, email, phone, gender, dob FROM users WHERE user_id = ?");
            $stmtPat->bind_param("i", $currentUserId);
            $stmtPat->execute();
            $patientData = $stmtPat->get_result()->fetch_assoc() ?: [];

            jsonResponse(true, "Doctor booking info retrieved", [
                'doctor'  => $doctorInfo,
                'patient' => $patientData
            ]);
            break;

        case "getDoctorProfile":
            $doctorId = (int)($_GET['doctor_id'] ?? $_POST['doctor_id'] ?? 0);
            if ($doctorId <= 0) {
                jsonResponse(false, "Doctor ID is required");
            }

            $doctorInfo = DoctorsService::getDoctorBookingInfo($conn, $doctorId);
            if (!$doctorInfo) {
                jsonResponse(false, "Doctor not found or inactive");
            }

            jsonResponse(true, "Doctor profile retrieved", [
                'doctor' => $doctorInfo
            ]);
            break;

        case "getDoctorDateSlots":
            $doctorId = (int)($_GET['doctor_id'] ?? $_POST['doctor_id'] ?? 0);
            $date = trim($_GET['date'] ?? $_POST['date'] ?? '');

            if ($doctorId <= 0 || empty($date)) {
                jsonResponse(false, "Doctor ID and valid Date are required");
            }

            $slotsData = DoctorsService::getDoctorBookingSlots($conn, $doctorId, $date);
            jsonResponse(true, "Slots retrieved successfully", $slotsData);
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
