<?php

require_once __DIR__ . "/../../models/AppointmentModel.php";

class AppointmentService {

    public static function getPatientsAppointmentsTableData(mysqli $conn, int $page, int $limit, int $userId, string $search, array $filters) {
        return AppointmentModel::getPatientsAppointmentsTableData($conn, $page, $limit, $userId, $search, $filters);
    }

    public static function bookAppointment(mysqli $conn, array $data): array {
        return AppointmentModel::bookAppointment($conn, $data);
    }

    public static function bookFollowUp(mysqli $conn, array $data): array {
        return AppointmentModel::bookFollowUp($conn, $data);
    }

    public static function acceptReschedule(mysqli $conn, int $appointmentId, int $patientId): array {
        return AppointmentModel::acceptRescheduleByPatient($conn, $appointmentId, $patientId);
    }

    public static function proposeAlternateSlot(mysqli $conn, int $appointmentId, int $patientId, string $newDate, string $newTime): array {
        return AppointmentModel::proposeAlternateSlotByPatient($conn, $appointmentId, $patientId, $newDate, $newTime);
    }

    public static function cancelAppointment(mysqli $conn, int $appointmentId, int $patientId, string $reason = ""): array {
        return AppointmentModel::cancelAppointmentByPatient($conn, $appointmentId, $patientId, $reason);
    }
}
