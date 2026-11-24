<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/helpers/auth.php";
require_once __DIR__ . "/../../../app/helpers/appointment.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/patient/AppointmentService.php";

try {
    $userId = requireRole('patient');
    if (!$userId) {
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

    $action = $_GET['action'] ?? $_POST['action'] ?? $jsonData['action'] ?? '';

    $page = max(1, (int)($_GET['page'] ?? $jsonData['page'] ?? 1));
    $limit = max(1, (int)($_GET['limit'] ?? $jsonData['limit'] ?? 10));
    $search = trim($_GET['search'] ?? $jsonData['search'] ?? '');

    $filters = [
        "user_type" => $_GET["user_type"] ?? $jsonData["user_type"] ?? "all",
        "verification_status" => $_GET["verification_status"] ?? $jsonData["verification_status"] ?? "pending",
        "status" => $_GET["status"] ?? $jsonData["status"] ?? "all",
        "department" => $_GET["department"] ?? $jsonData["department"] ?? "",
        "sort" => $_GET["sort"] ?? $jsonData["sort"] ?? "",
        "date_from" => $_GET["date_from"] ?? $jsonData["date_from"] ?? "",
        "date_to" => $_GET["date_to"] ?? $jsonData["date_to"] ?? ""
    ];

    switch ($action) {
        
        case "table":
            $data = AppointmentService::getPatientsAppointmentsTableData(
                $conn,
                $page,
                $limit,
                $userId,
                $search,
                $filters
            );

            jsonResponse(
                true,
                "Appointment Data Fetched Successfully",
                $data
            );
            break;

        case "bookAppointment":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $payload['patient_id'] = $userId;

            $result = AppointmentService::bookAppointment($conn, $payload);
            jsonResponse(
                true,
                $result['message'] ?? "Appointment booked successfully!",
                $result
            );
            break;

        case "bookFollowUp":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $payload['patient_id'] = $userId;

            $result = AppointmentService::bookFollowUp($conn, $payload);
            jsonResponse(
                true,
                $result['message'] ?? "Follow-up booked successfully!",
                $result
            );
            break;

        case "acceptReschedule":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            if ($appointmentId <= 0) {
                throw new Exception("Valid appointment ID is required.");
            }
            $result = AppointmentService::acceptReschedule($conn, $appointmentId, $userId);
            jsonResponse(true, $result['message'], $result);
            break;

        case "proposeAlternateSlot":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            $newDate = trim($payload['appointment_date'] ?? '');
            $newTime = trim($payload['appointment_time'] ?? '');
            if ($appointmentId <= 0 || empty($newDate) || empty($newTime)) {
                throw new Exception("Appointment ID, date, and time slot are required.");
            }
            $result = AppointmentService::proposeAlternateSlot($conn, $appointmentId, $userId, $newDate, $newTime);
            jsonResponse(true, $result['message'], $result);
            break;

        case "cancelAppointment":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            $reason = trim($payload['reason'] ?? '');
            if ($appointmentId <= 0) {
                throw new Exception("Valid appointment ID is required.");
            }
            $result = AppointmentService::cancelAppointment($conn, $appointmentId, $userId, $reason);
            jsonResponse(true, $result['message'], $result);
            break;
        
        default:
            http_response_code(400);
            jsonResponse(false, "Invalid action: " . htmlspecialchars($action));
            break;
    }

} catch (Throwable $e) {
    http_response_code(400);
    jsonResponse(
        false,
        $e->getMessage()
    );
}
