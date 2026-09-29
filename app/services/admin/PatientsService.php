<?php

require_once __DIR__ . "/../../models/UserModel.php";

class PatientsService{

    public static function getPatientsPageData(mysqli $conn): array{
        return UserModel::getPatientsStats($conn);
    }

    public static function getPatientsTableData(mysqli $conn, int $page, int $limit, string $search, array $filters): array{
        return UserModel::getPatientsTableData($conn, $page, $limit, $search, $filters);
    }

}