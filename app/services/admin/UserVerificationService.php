<?php

require_once __DIR__ . "/../../models/DoctorModel.php";

class UserVerificationService {

    public static function getVerificationStatsData(mysqli $conn): array {
        return DoctorModel::getVerificationStats($conn);
    }

    public static function getVerificationTableData(mysqli $conn, int $page, int $limit, string $search, array $filters): array {
        return DoctorModel::getVerificationTable($conn, $page, $limit, $search, $filters);
    }

    public static function approve(mysqli $conn, int $doctorId, int $adminId): array {
        return DoctorModel::updateVerificationStatus($conn, $doctorId, 'verified', $adminId);
    }

    public static function reject(mysqli $conn, int $doctorId, int $adminId, string $reason = ''): array {
        return DoctorModel::updateVerificationStatus($conn, $doctorId, 'rejected', $adminId, $reason);
    }

}