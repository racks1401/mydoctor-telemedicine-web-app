<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/helpers/auth.php";
require_once __DIR__ . "/../../../app/helpers/appointment.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/doctor/AppointmentService.php";

try {
    $doctorId = requireRole('doctor');
    if (!$doctorId) {
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
        "status" => $_GET["status"] ?? $jsonData["status"] ?? "all",
        "date"   => $_GET["date"] ?? $jsonData["date"] ?? ""
    ];

    switch ($action) {
        
        case "table":
        case "list":
            $data = DoctorAppointmentService::getDoctorAppointmentsTableData(
                $conn,
                $page,
                $limit,
                $doctorId,
                $search,
                $filters
            );

            jsonResponse(
                true,
                "Doctor appointments fetched successfully",
                $data
            );
            break;

        case "confirm":
        case "confirmAppointment":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            if ($appointmentId <= 0) {
                throw new Exception("Valid appointment ID is required.");
            }

            $result = DoctorAppointmentService::confirmAppointment($conn, $appointmentId, $doctorId);
            jsonResponse(true, $result['message'], $result);
            break;

        case "getAvailableSlots":
        case "getDoctorDateSlots":
            require_once __DIR__ . "/../../../app/models/DoctorModel.php";
            $date = trim($_GET['date'] ?? $_POST['date'] ?? $jsonData['date'] ?? '');
            if (empty($date)) {
                throw new Exception("Valid date is required.");
            }
            $slotsData = DoctorModel::getDoctorBookingSlots($conn, $doctorId, $date);
            jsonResponse(true, "Doctor slots fetched successfully", $slotsData);
            break;

        case "cancel":
        case "cancelAppointment":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            $reason = trim($payload['reason'] ?? $payload['cancellation_reason'] ?? '');
            if ($appointmentId <= 0) {
                throw new Exception("Valid appointment ID is required.");
            }

            $result = DoctorAppointmentService::cancelAppointment($conn, $appointmentId, $doctorId, $reason);
            jsonResponse(true, $result['message'], $result);
            break;

        case "reschedule":
        case "rescheduleAppointment":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            $proposedDate = trim($payload['proposed_date'] ?? '');
            $proposedStartTime = trim($payload['proposed_start_time'] ?? '');
            $proposedEndTime = trim($payload['proposed_end_time'] ?? '');

            if ($appointmentId <= 0) {
                throw new Exception("Valid appointment ID is required.");
            }
            if (empty($proposedDate) || empty($proposedStartTime)) {
                throw new Exception("Proposed date and start time are required.");
            }

            $result = DoctorAppointmentService::rescheduleAppointment(
                $conn,
                $appointmentId,
                $doctorId,
                $proposedDate,
                $proposedStartTime,
                $proposedEndTime ?: null
            );
            jsonResponse(true, $result['message'], $result);
            break;

        case "complete":
        case "completeAppointment":
            $payload = !empty($jsonData) ? $jsonData : $_POST;
            $appointmentId = (int)($payload['appointment_id'] ?? 0);
            if ($appointmentId <= 0) {
                throw new Exception("Valid appointment ID is required.");
            }

            $result = DoctorAppointmentService::completeAppointment($conn, $appointmentId, $doctorId);
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
