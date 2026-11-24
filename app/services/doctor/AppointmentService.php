<?php
require_once __DIR__ . "/../../models/AppointmentModel.php";

class DoctorAppointmentService {

    public static function getDoctorAppointmentsTableData(
        mysqli $conn, int $page, int $limit, int $doctorId, string $search, array $filters
    ): array {
        return AppointmentModel::getDoctorAppointmentsTableData($conn, $page, $limit, $doctorId, $search, $filters);
    }

    public static function confirmAppointment(mysqli $conn, int $appointmentId, int $doctorId): array {
        return AppointmentModel::confirmAppointmentByDoctor($conn, $appointmentId, $doctorId);
    }

    public static function cancelAppointment(mysqli $conn, int $appointmentId, int $doctorId, string $reason): array {
        return AppointmentModel::cancelAppointmentByDoctor($conn, $appointmentId, $doctorId, $reason);
    }

    public static function rescheduleAppointment(
        mysqli $conn, int $appointmentId, int $doctorId, string $proposedDate, string $proposedStartTime, ?string $proposedEndTime = null
    ): array {
        return AppointmentModel::rescheduleAppointmentByDoctor($conn, $appointmentId, $doctorId, $proposedDate, $proposedStartTime, $proposedEndTime);
    }

    public static function completeAppointment(mysqli $conn, int $appointmentId, int $doctorId): array {
        return AppointmentModel::completeAppointmentByDoctor($conn, $appointmentId, $doctorId);
    }
}
