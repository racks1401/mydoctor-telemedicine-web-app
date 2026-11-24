<?php

require_once __DIR__ ."/../../models/UserModel.php";
require_once __DIR__ ."/../../models/DoctorModel.php";
require_once __DIR__ ."/../../models/AppointmentModel.php";

class DashboardService {

    public static function getProfileData(mysqli $conn, int $userId): array {
        return UserModel::getAdminProfileData($conn, $userId);
    }

    public static function updatePreference(mysqli $conn, int $userId, array $data): array {
        return UserModel::updatePreference($conn, $userId, $data);
    }

    public static function changePassword(mysqli $conn, int $userId, array $data): array {
        return UserModel::changePassword($conn, $userId, $data);
    }

    public static function getDashboardData(mysqli $conn, int $userId): array {
        return [
            "currentUser" => UserModel::getUserById($conn, $userId),
            "stats" => [
                "totalPatientsCount" => UserModel::getPatientsCount($conn, false),
                "activeDoctorsCount" => DoctorModel::getDoctorsCount($conn, true),
                "appointmentsTodayCount" => AppointmentModel::countTodayAppointments($conn),
                "blockedUsersCount" => UserModel::getBlockedUsersCount($conn)
            ],
            "recentAppointments" => AppointmentModel::getRecentAppointments($conn),
            "alerts" => self::getSystemAlerts($conn)
        ];
    }

    public static function getSystemAlerts(mysqli $conn): array {
        $alerts = [];

        // 1. Pending doctor verifications
        $docQuery = "
            SELECT d.user_id, u.name, d.specialization, d.verification_status, u.created_at
            FROM doctors d
            JOIN users u ON d.user_id = u.user_id
            WHERE d.verification_status = 'pending'
            ORDER BY u.created_at DESC
            LIMIT 2
        ";
        $docRes = $conn->query($docQuery);
        if ($docRes) {
            while ($doc = $docRes->fetch_assoc()) {
                $alerts[] = [
                    "title" => "Doctor Verification Required",
                    "body" => "Dr. {$doc['name']} ({$doc['specialization']}) submitted credentials awaiting verification.",
                    "type" => "warning",
                    "time" => $doc['created_at']
                ];
            }
        }

        // 2. Recent Blocked / Unblocked events
        $blockQuery = "
            SELECT bl.log_id, bl.action, bl.reason, bl.created_at, u.name as user_name, u.user_type
            FROM blocked_logs bl
            LEFT JOIN users u ON bl.user_id = u.user_id
            ORDER BY bl.created_at DESC
            LIMIT 2
        ";
        $blockRes = $conn->query($blockQuery);
        if ($blockRes) {
            while ($b = $blockRes->fetch_assoc()) {
                $isUnblock = ($b['action'] === 'unblocked');
                $role = ($b['user_type'] === 'doctor') ? 'Doctor' : 'Patient';
                $alerts[] = [
                    "title" => $isUnblock ? "Account Access Restored" : "Security Notice: {$role} Suspended",
                    "body" => ($b['user_name'] ?? 'User') . ": " . $b['reason'],
                    "type" => $isUnblock ? "success" : "error",
                    "time" => $b['created_at']
                ];
            }
        }

        // 3. Appointments load / queue
        $todayAppts = AppointmentModel::countTodayAppointments($conn);
        if ($todayAppts > 0) {
            $alerts[] = [
                "title" => "Today's Appointment Schedule",
                "body" => "There are {$todayAppts} active appointments scheduled across departments for today.",
                "type" => "primary",
                "time" => date("Y-m-d H:i:s")
            ];
        } else {
            $pendingAppts = 0;
            $pRes = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status = 'pending'");
            if ($pRes) $pendingAppts = (int)$pRes->fetch_assoc()['c'];
            $alerts[] = [
                "title" => "Appointment Queue Status",
                "body" => "{$pendingAppts} pending appointment bookings in queue awaiting doctor confirmation.",
                "type" => "primary",
                "time" => date("Y-m-d H:i:s")
            ];
        }

        // Sort alerts by time descending
        usort($alerts, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($alerts, 0, 4);
    }
}
