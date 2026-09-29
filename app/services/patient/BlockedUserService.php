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

    public static function unblockUser(mysqli $conn, int $id): bool {
        return UserModel::unblockUser($conn, $id);
    }

}