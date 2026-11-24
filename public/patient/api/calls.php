<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../app/helpers/auth.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/patient/CallsService.php";

try {
    $patientId = requireRole('patient');
    if (!$patientId) {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access. Please log in.");
        exit;
    }

    $type = $_GET['type'] ?? 'all';
    $search = $_GET['search'] ?? '';

    $data = PatientCallsService::getCallsData($conn, $patientId, [
        'type'   => $type,
        'search' => $search
    ]);

    jsonResponse(true, "Patient calls history fetched successfully", $data);

} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, "Server error: " . $e->getMessage());
}
