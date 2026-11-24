<?php

require_once __DIR__ . "/../../models/DoctorModel.php";
require_once __DIR__ . "/../../models/UserModel.php";

class BlockedUserService {

    public static function getBlockedStatsData(mysqli $conn): array {
        return DoctorModel::getBlockedStatsData($conn);
    }

    public static function getBlockedTableData(mysqli $conn, int $page, int $limit, string $search, array $filters): array {
        return DoctorModel::getBlockedTableData($conn, $page, $limit, $search, $filters);
    }

    public static function unblockUser(mysqli $conn, int $id, int $adminId = 0): bool {
        return UserModel::unblockUser($conn, $id, $adminId);
    }

    public static function getAuditLogs(mysqli $conn, int $limit = 4): array {
        return DoctorModel::getRestrictionAuditLogs($conn, $limit);
    }

    public static function getPolicies(mysqli $conn): array {
        return DoctorModel::getRestrictionPolicies($conn);
    }

}
