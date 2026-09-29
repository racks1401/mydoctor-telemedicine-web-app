<?php

require_once __DIR__ . "/../../models/AppointmentModel.php";

class AppointmentService{

    public static function getPatientsAppointmentsTableData(mysqli $conn, int $page, int $limit, int $userId, string $search, array $filters){
        return AppointmentModel::getPatientsAppointmentsTableData($conn, $page, $limit, $userId, $search, $filters);
    }
}


