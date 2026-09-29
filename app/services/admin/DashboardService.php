<?php

require_once __DIR__ ."/../../models/UserModel.php";
require_once __DIR__ ."/../../models/DoctorModel.php";

require_once __DIR__ ."/../../models/AppointmentModel.php";


class DashboardService {

    public static function getProfileData(mysqli $conn, int $userId): array {
        return UserModel::getAdminProfileData($conn, $userId);

    }

    public static function updatePreference(mysqli $conn, int $userId, array $data): array{
        return UserModel::updatePreference($conn, $userId, $data);
    }

    public static function changePassword(mysqli $conn, int $userId, array $data): array {
        return UserModel::changePassword($conn, $userId, $data);
    }

    public static function getDashboardData(mysqli $conn, int $userId): array {
        return [

            "currentUser" =>
                UserModel::getUserById(
                    $conn,
                    $userId
                ),

            "stats" => [

                "totalPatientsCount" =>
                    UserModel::getPatientsCount(
                        $conn,
                        false
                    ),

                "activeDoctorsCount" =>
                    DoctorModel::getDoctorsCount(
                        $conn,
                        true
                    ),

                "appointmentsTodayCount" =>
                    AppointmentModel::countTodayAppointments(
                        $conn
                    ),

                "blockedUsersCount" =>
                    UserModel::getBlockedUsersCount(
                        $conn
                    )

            ],

            "recentAppointments" =>
                AppointmentModel::getRecentAppointments(
                    $conn
                )

        ];

    }

}