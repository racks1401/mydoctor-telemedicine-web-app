<?php

require_once __DIR__ . "/../../models/DoctorModel.php";

class DoctorsService{
    public static function getDoctorsPageData(mysqli $conn): array{
        return DoctorModel::getDoctorsStats($conn);
    }

    public static function getPatientsDoctorsTableData(mysqli $conn, int $page, int $limit, string $search, array $filters): array{
        return DoctorModel::getPatientsDoctorsTableData($conn, $page, $limit, $search, $filters);
    }

    public static function getDoctorBookingInfo(mysqli $conn, int $doctorId): ?array {
        return DoctorModel::getDoctorBookingInfo($conn, $doctorId);
    }

    public static function getDoctorBookingSlots(mysqli $conn, int $doctorId, string $date): array {
        return DoctorModel::getDoctorBookingSlots($conn, $doctorId, $date);
    }
}

?>
