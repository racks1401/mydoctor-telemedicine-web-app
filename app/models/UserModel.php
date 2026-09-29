<?php

class UserModel {

    public static function getUserById(
        mysqli $conn,
        int $userId
    ): array {

        $stmt = $conn->prepare("
            SELECT
                user_id,
                name,
                email,
                profile_url,
                user_type
            FROM users
            WHERE user_id = ?
        ");

        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();

    }

    public static function getAdminProfileData(
        mysqli $conn,
        int $userId
    ): array {

        $stmt = $conn->prepare("
            SELECT
                user_id,
                name,
                email,
                profile_url,
                status,
                user_type,
                phone,
                created_at,
                theme,
                email_notifications,
                sms_notifications,
                last_login_at,
                last_login_ip
            FROM users
            WHERE user_id = ?
        ");

        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();

    }

    public static function getPatientProfileData(
        mysqli $conn,
        int $userId
    ): array {

        $stmt = $conn->prepare("
            SELECT
                user_id,
                name,
                email,
                profile_url,
                status,
                user_type,
                phone,
                created_at,
                theme,
                email_notifications,
                sms_notifications,
                last_login_at,
                last_login_ip
            FROM users
            WHERE user_id = ?
        ");

        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        return $stmt
            ->get_result()
            ->fetch_assoc();

    }

    public static function updatePreference(
    mysqli $conn,
    int $userId,
    array $data
    ): array {

        $key = $data["key"];
        $value = $data["value"];

        $allowed = [
            "theme",
            "email_notifications",
            "sms_notifications"
        ];

        if (!in_array($key, $allowed, true)) {
            return [
                "status" => false,
                "message" => "Invalid preference."
            ];

        }

        $query = "
            UPDATE users
            SET {$key} = ?
            WHERE user_id = ?
        ";

        $stmt = $conn->prepare($query);

        if ($key === "theme") {

            $stmt->bind_param("si", $value, $userId);

        } else {

            $stmt->bind_param("ii", $value, $userId);

        }

        if (!$stmt->execute()) {

            return [
                "status" => false,
                "message" => "Database error."
            ];

        }

        return [
            "status" => true,
            "message" => "Preference updated."
        ];

    }

    public static function changePassword(mysqli $conn, int $userId, array $data): array{
        
        $currentPassword = $data["currentPassword"];
        $newPassword = $data["newPassword"];

        $stmt = $conn->prepare("
            SELECT password
            FROM users
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            return [
                "status" => false,
                "message" => "User not found."
            ];
        }

        if (!password_verify($currentPassword, $user["password"])) {
            return [
                "status" => false,
                "message" => "Current password is incorrect."
            ];
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE user_id = ?
        ");
        $updateStmt->bind_param("si", $newPasswordHash, $userId);

        if (!$updateStmt->execute()) {
            return [
                "status" => false,
                "message" => "Failed to update password."
            ];
        }

        return [
            "status" => true,
            "message" => "Password changed successfully."
        ];
    }

    public static function getPatientsStats(
    mysqli $conn
    ): array {

        $totalPatients =
            self::getPatientsCount($conn);

        $activePatients =
            self::getPatientsCount(
                $conn,
                true
            );

        $blockedPatients =
            self::getBlockedUsersCount(
                $conn,
                'patient'
            );

        $thisMonthPatients =
            self::getThisMonthPatientsStats(
                $conn
            );

        $activePercentage =
            $totalPatients > 0
                ? round(
                    ($activePatients / $totalPatients) * 100
                )
                : 0;

        $blockedPercentage =
            $totalPatients > 0
                ? round(
                    ($blockedPatients / $totalPatients) * 100
                )
                : 0;


        return [

            "total" => [
                "count" => $totalPatients
            ],

            "active" => [
                "count" => $activePatients,
                "percentage" => $activePercentage
            ],

            "new_this_month" => $thisMonthPatients,

            "blocked" => [
                "count" => $blockedPatients,
                "percentage" => $blockedPercentage
            ]

        ];

        
    }

    

    public static function getPatientsCount(
    mysqli $conn,
    bool $onlyActive = false
    ): int{
        $query = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE user_type = 'patient'
        ";

        if ($onlyActive) {

            $query .= "
                AND status = 'active'
            ";

        }

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    public static function getBlockedUsersCount(
        mysqli $conn,
        ?string $userType = null
    ):int{

        $query = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE status = 'blocked'
        ";

        if($userType !== null){
            $query .= "
                AND user_type = '$userType'
            ";
        }

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    

    public static function getThisMonthPatientsStats(mysqli $conn): array
    {
        // Current month
        $currentQuery = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE user_type = 'patient'
            AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        ";

        $currentResult = $conn->query($currentQuery);
        $currentCount = (int) $currentResult->fetch_assoc()['total'];

        // Previous month
        $previousQuery = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE user_type = 'patient'
            AND created_at >= DATE_FORMAT(
                CURDATE() - INTERVAL 1 MONTH,
                '%Y-%m-01'
            )
            AND created_at < DATE_FORMAT(
                CURDATE(),
                '%Y-%m-01'
            )
        ";

        $previousResult = $conn->query($previousQuery);
        $previousCount = (int) $previousResult->fetch_assoc()['total'];

        // Calculate trend
        if ($previousCount > 0) {

            $trend = (
                ($currentCount - $previousCount)
                / $previousCount
            ) * 100;

        } else {

            $trend = $currentCount > 0 ? 100 : 0;

        }

        return [
            'count' => $currentCount,
            'trend' => round($trend, 1),
            'direction' => $trend >= 0 ? 'up' : 'down'
        ];
    }

    public static function getPatientsTableData(
    mysqli $conn,
    int $page,
    int $limit,
    string $search,
    array $filters
    ): array {
    
        $where = ["user_type = 'patient'"];
        $params = [];
        $types = "";

        if (!empty($filters["status"]) && $filters["status"] != "all") {
            $where[] = "status = ?";
            $params[] = $filters["status"];
            $types .= "s";
        }

        if (!empty($search)) {
            $where[] = "(name LIKE ? OR email LIKE ? OR CAST(user_id AS CHAR) LIKE ?)";
            $search = "%" . trim($search) . "%";

            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $whereSql = implode(" AND ", $where);


        $countQuery = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE $whereSql
        ";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $countResult = $stmt->get_result();
        $totalPatients = (int)$countResult->fetch_assoc()["total"];

        $totalPages = max(1, (int) ceil($totalPatients / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $query = "
        SELECT
            user_id,
            name,
            email,
            dob,
            status,
            last_login_at
        FROM users
        WHERE $whereSql
        ORDER BY created_at DESC
        LIMIT ?
        OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result =
            $stmt->get_result();

        $patients = [];

        while ($row = $result->fetch_assoc()) 
        {

            $patients[] = $row;

        }

        return [

            "patients" => $patients,

            "pagination" => [

                "page" => $page,

                "limit" => $limit,

                "total" => $totalPatients,

                "totalPages" => $totalPages

            ]

        ];

    }
    
    public static function unblockUser(mysqli $conn, int $id): bool
    {
        $query = "
            UPDATE users
            SET status = 'active'
            WHERE user_id = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    
}