<?php

require_once __DIR__ . "/../../models/DoctorModel.php";

class DoctorsService{
    public static function getDoctorsPageData(mysqli $conn): array{
        return DoctorModel::getDoctorsStats($conn);
    }

    public static function getDoctorsTableData(mysqli $conn, int $page, int $limit, string $search, array $filters): array{
        return DoctorModel::getDoctorsTableData($conn, $page, $limit, $search, $filters);
    }
}


?>