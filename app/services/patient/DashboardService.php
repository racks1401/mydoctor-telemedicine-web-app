<?php

require_once __DIR__ ."/../../models/UserModel.php";

require_once __DIR__ ."/../../models/AppointmentModel.php";


class DashboardService {

    public static function getUserById(mysqli $conn, int $userId): array {
        return UserModel::getUserById($conn, $userId);

    }

    public static function getProfileData(mysqli $conn, int $userId): array {
        return UserModel::getPatientProfileData($conn, $userId);

    }

    public static function updatePreference(mysqli $conn, int $userId, array $data): array{
        return UserModel::updatePreference($conn, $userId, $data);
    }

    public static function changePassword(mysqli $conn, int $userId, array $data): array {
        return UserModel::changePassword($conn, $userId, $data);
    }

    public static function getDashboardData(mysqli $conn, int $userId): array {
        return [


            "stats" => [
                "totalAppointments" =>
                    AppointmentModel::getTotalAppointments(
                        $conn,
                        $userId
                    ),

                "completedAppointments" =>
                    AppointmentModel::getTotalCompletedAppointments(
                        $conn,
                        $userId
                    ),

                "totalDoctorsConsulted" =>
                    AppointmentModel::getTotalDoctorsConsulted(
                        $conn,
                        $userId
                    ),

                "pendingAppointments" =>
                    AppointmentModel::getTotalPendingAppointments(
                        $conn,
                        $userId
                    ),

            ],

            "recentAppointment" =>
                AppointmentModel::getRecentPatientAppointment(
                    $conn,
                    $userId  
                ),
            
            "upcomingAppointment" =>
                AppointmentModel::getUpcomingPatientAppointment(
                    $conn,
                    $userId
                )   

        ];

    }

}