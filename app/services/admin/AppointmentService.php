<?php

require_once __DIR__ . "/../../models/AppointmentModel.php";

class AppointmentService{

    public static function getAppointmentStats(mysqli $conn){
        return AppointmentModel::getAppointmentStats($conn);
    }

    public static function getAppointmentsTableData(mysqli $conn, int $page, int $limit, string $search, array $filters){
        return AppointmentModel::getAdminAppointmentsTableData($conn, $page, $limit, $search, $filters);
    }
}
