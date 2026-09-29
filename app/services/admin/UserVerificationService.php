<?php

require_once __DIR__ . "/../../models/DoctorModel.php";

class UserVerificationService {

    public static function getVerificationStatsData(mysqli $conn): array {
        return DoctorModel::getVerificationStats($conn);
    }

    public static function getVerificationTableData(mysqli $conn, int $page, int $limit, string $search, array $filters): array {
        return DoctorModel::getVerificationTable($conn, $page, $limit, $search, $filters);
    }

}