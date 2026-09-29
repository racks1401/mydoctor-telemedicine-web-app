<?php

class DoctorModel{

    public static function getTotalUsersCount(
        mysqli $conn,
        ): int {

            $query = "
                SELECT COUNT(*) AS total
                FROM users
            ";

            $result = $conn->query($query);

            return (int)
                $result->fetch_assoc()['total'];
    }

    public static function getDoctorsCount(
    mysqli $conn,
    bool $onlyActive = false
    ): int {

        $query = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE user_type='doctor'
        ";

        if ($onlyActive) {
            $query .= "
                AND status='active'
            ";

        }

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    public static function getPendingVerificationCount(
        mysqli $conn,
    ):int{

        $query = "
            SELECT COUNT(*) AS total
            FROM doctors
            WHERE verification_status = 'pending'
        ";

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    public static function getAvgReviewTime(
        mysqli $conn
    ): float {

        $query = "
            SELECT AVG(
                TIMESTAMPDIFF(
                    HOUR,
                    u.created_at,
                    d.verified_at
                )
            ) AS avg_review_time
            FROM doctors d
            INNER JOIN users u
                ON d.user_id = u.user_id
            WHERE d.verification_status = 'verified'
            AND d.verified_at IS NOT NULL
        ";

        $result = $conn->query($query);

        $avgTime = $result->fetch_assoc()['avg_review_time'];

        return round(
            (float) ($avgTime ?? 0),
            1
        );
    }

    public static function getTodayPendingVerificationCount(
        mysqli $conn,
    ):int{

        $query = "
            SELECT COUNT(*) AS total
            FROM doctors
            WHERE verification_status = 'pending'
            AND verified_at >= CURDATE()
            AND verified_at < CURDATE() + INTERVAL 1 DAY
        ";

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    public static function getApprovedVerificationCount(
        mysqli $conn,
    ):int{

        $query = "
            SELECT COUNT(*) AS total
            FROM doctors
            WHERE verification_status = 'verified'
        ";

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    public static function getRejectedVerificationCount(
        mysqli $conn,
    ):int{

        $query = "
            SELECT COUNT(*) AS total
            FROM doctors
            WHERE verification_status = 'rejected'
        ";

        $result = $conn->query($query);

        return (int)
            $result->fetch_assoc()['total'];
    }

    public static function getTotalBlockedDoctorsCount(
        mysqli $conn,
        ): int {

            $query = "
                SELECT COUNT(*) AS total
                FROM users
                WHERE status = 'blocked'
                AND user_type = 'doctor'
            ";

            $result = $conn->query($query);

            return (int)
                $result->fetch_assoc()['total'];
    }

    public static function getTotalBlockedPatientsCount(
        mysqli $conn,
        ): int {

            $query = "
                SELECT COUNT(*) AS total
                FROM users
                WHERE status = 'blocked'
                AND user_type = 'patient'
            ";

            $result = $conn->query($query);

            return (int)
                $result->fetch_assoc()['total'];
    }

    public static function getBlockedAppealCount(
        mysqli $conn,
        ): int {

            $query = "
                SELECT COUNT(*) AS total
                FROM blocked_appeal
            ";

            $result = $conn->query($query);

            return (int)
                $result->fetch_assoc()['total'];
    }

// ============================ Doctors Page Data ===================================


    public static function getDoctorsStats(
    mysqli $conn
    ): array {

        $totalDoctors =
            self::getDoctorsCount($conn);

        $activeDoctors =
            self::getDoctorsCount(
                $conn,
                true
            );

        $pendingVerificationDoctors =
            self::getPendingVerificationCount(
                $conn
            );

        $blockedDoctors =
            self::getTotalBlockedDoctorsCount(
                $conn,
                'doctor'
            );

        $activePercentage =
            $totalDoctors > 0
                ? round(
                    ($activeDoctors / $totalDoctors) * 100
                )
                : 0;

        $blockedPercentage =
            $totalDoctors > 0
                ? round(
                    ($blockedDoctors / $totalDoctors) * 100
                )
                : 0;


        return [

            "total" => [
                "count" => $totalDoctors,
            ],

            "active" => [
                "count" => $activeDoctors,
                "percentage" => $activePercentage
            ],

            "pending_verification" =>[
                "count" => $pendingVerificationDoctors,
            ],

            "blocked" => [
                "count" => $blockedDoctors,
                "percentage" => $blockedPercentage
            ]

        ];

        
    }

    public static function getDoctorsTableData(
        mysqli $conn,
        int $page,
        int $limit,
        string $search,
        array $filters
        ): array{

            $where = ["u.user_type = 'doctor'"];
            $params = [];
            $types = "";

            if(!empty($filters["status"]) && $filters["status"] != "all"){
                $where[] = "u.status = ?";
                $params[] = $filters["status"];
                $types .="s";

            }

            if (!empty($search)) {
                $where[] = "(u.name LIKE ? OR u.email LIKE ? OR CAST(u.user_id AS CHAR) LIKE ?)";
                $search = "%" . trim($search) . "%";

                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $types .= "sss";
            }

            $whereSql = implode(" AND ", $where);

            $countQuery = "
                SELECT COUNT(*) AS total
                FROM users u
                WHERE $whereSql
            ";

            $stmt = $conn->prepare($countQuery);

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();

            $countResult = $stmt->get_result();
            $totalDoctors = (int)$countResult->fetch_assoc()["total"];

            $totalPages = max(1, (int) ceil($totalDoctors / $limit));

            if ($page > $totalPages) {
                $page = $totalPages;
            }

            if ($page < 1) {
                $page = 1;
            }

            $offset = ($page - 1) * $limit;


            $query = "
            SELECT
                u.user_id,
                u.name,
                u.profile_url,
                u.status,
                u.created_at,
                d.specialization,
                d.department
            FROM doctors d
            INNER JOIN users u
                ON d.user_id = u.user_id
            WHERE $whereSql
            ORDER BY u.name ASC
            LIMIT ?
            OFFSET ?
        ";

        $stmt = $conn->prepare($query);

        $dataParams = $params;
        $dataTypes = $types;


        $dataParams[] = $limit;
        $dataParams[] = $offset;
        $dataTypes .= "ii";

        $stmt->bind_param(
            $dataTypes,
            ...$dataParams
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $doctors = [];

        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }

            return [
                "doctors" => $doctors,

                "pagination" => [
                    "page" => $page,

                    "limit" => $limit,

                    "total" => $totalDoctors,

                    "totalPages" => $totalPages
                ]
            ];

            
    }

    // ========================== User-Verification Page Data Queries ==========================

    public static function getVerificationStats(
    mysqli $conn
    ): array {

        $pendingReview =
            self::getPendingVerificationCount($conn);

        $approved =
            self::getApprovedVerificationCount(
                $conn);

        $rejected =
            self::getRejectedVerificationCount(
                $conn
            );

        $avgReviewTime =
            self::getAvgReviewTime(
                $conn
            );

        $todayTrend = self::getTodayPendingVerificationCount($conn);

        return [

            "totalPendingReview" => [
                "count" => $pendingReview,
                "trend" => $todayTrend
            ],

            "totalApprovedReview" => [
                "count" => $approved
            ],

            "rejected" => [
                "count" => $rejected,
            ],

            "avgReviewTime" => $avgReviewTime

        ];

    }

    public static function getVerificationTable(
    mysqli $conn,
    int $page,
    int $limit,
    string $search,
    array $filters
    ): array {

        $where = ["u.user_type = 'doctor'"];
        $params = [];
        $types = "";

        if (!empty($filters["verification_status"])) {
            $where[] = "d.verification_status = ?";
            $params[] = $filters["verification_status"];
            $types .= "s";
        }

        if (!empty($search)) {
            $where[] = "(
                u.name LIKE ?
                OR u.email LIKE ?
                OR CAST(u.user_id AS CHAR) LIKE ?
            )";

            $searchValue = "%" . trim($search) . "%";

            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;

            $types .= "sss";
        }

        $whereSql = implode(" AND ", $where);
        
        // Query Will be use after completing the doctors table

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM users u
            INNER JOIN doctors d
                ON d.user_id = u.user_id
            WHERE $whereSql
        ";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $totalDoctors = (int)$result->fetch_assoc()["total"];

        $totalPages = max(1, (int)ceil($totalDoctors / $limit));

        $page = max(1, min($page, $totalPages));

        $offset = ($page - 1) * $limit;

        $query = "
            SELECT
                u.user_id,
                u.name,
                u.email,
                u.profile_url,
                u.user_type,

                d.specialization,
                d.department,
                d.verification_status,

                GROUP_CONCAT(
                    DISTINCT vd.document_type
                    ORDER BY vd.document_type
                    SEPARATOR ' + '
                ) AS document_types,

                MIN(vd.uploaded_at) AS submitted_at

            FROM users u

            INNER JOIN doctors d
                ON d.user_id = u.user_id

            LEFT JOIN verification_documents vd
                ON vd.user_id = u.user_id

            WHERE $whereSql

            GROUP BY
                u.user_id

            ORDER BY submitted_at DESC

            LIMIT ? OFFSET ?
        ";

        $stmt = $conn->prepare($query);

        $tableParams = $params;
        $tableParams[] = $limit;
        $tableParams[] = $offset;

        $tableTypes = $types . "ii";

        $stmt->bind_param($tableTypes, ...$tableParams);

        $stmt->execute();

        $result = $stmt->get_result();

        $doctors = [];

        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }

        return [
            "doctors" => $doctors,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $totalDoctors,
                "totalPages" => $totalPages
            ]
        ];
    }

    // ============================= Blocked Users Page Data =============================

    public static function getBlockedStatsData(
    mysqli $conn
    ): array {

        $totalBlocked = self::getTotalBlockedDoctorsCount($conn)+self::getTotalBlockedPatientsCount($conn);

        $doctorsBlocked =
            self::getTotalBlockedDoctorsCount(
                $conn);

        $patientsBlocked =
            self::getTotalBlockedPatientsCount(
                $conn
            );

        $appealCount = 0;

        $doctorTrend = ($doctorsBlocked / $totalBlocked) * 100;
        $patientTrend = ($patientsBlocked / $totalBlocked) * 100;

        return [

            "totalBlocked" => self::getTotalBlockedStats($conn),

            "doctorsBlocked" => [
                "count" => $doctorsBlocked,
                "trend" => round($doctorTrend, 0)
            ],

            "patientsBlocked" => [
                "count" =>  $patientsBlocked,
                "trend" => round($patientTrend, 0)
            ],

            "appealBlocked" => [
                "count" => $appealCount,
                "trend" => 0
            ]

        ];

    }

    public static function getTotalBlockedStats(mysqli $conn): array
    {

        // Current total
        $currentQuery = "
            SELECT COUNT(*) AS total_blocked
            FROM users
            WHERE status = 'blocked';
        ";
         
        $currentResult = $conn->query($currentQuery);
        $currentCount = (int) $currentResult->fetch_assoc()['total_blocked'];

        // Today day
        $todayQuery = "
            SELECT COUNT(*) AS today_count
            FROM blocked_logs
            WHERE action = 'blocked'
            AND created_at >= CURDATE()
            AND created_at < CURDATE() + INTERVAL 1 DAY;
        ";

        $todayResult = $conn->query($todayQuery);
        $todayCount = (int) $todayResult->fetch_assoc()['today_count'];


        return [
            'count' => $currentCount,
            'trend' => $todayCount,
        ];
    }

    public static function getBlockedAppealStats(mysqli $conn): array
    {
        // Current day
        $currentQuery = "
            SELECT COUNT(*) AS total
            FROM users
            WHERE user_type = 'patient'
            AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        ";

        $currentResult = $conn->query($currentQuery);
        $currentCount = (int) $currentResult->fetch_assoc()['total'];

        // Previous previous
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
        ];
    }


    public static function getBlockedTableData(
    mysqli $conn,
    int $page,
    int $limit,
    string $search,
    array $filters
    ): array {

        $where = ["u.status = 'blocked'"];
        $params = [];
        $types = "";

        // User Type Filter
        if (!empty($filters["user_type"]) && $filters["user_type"] !== "all") {
            $where[] = "u.user_type = ?";
            $params[] = $filters["user_type"];
            $types .= "s";
        }

        if (!empty($search)) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR CAST(u.user_id AS CHAR) LIKE ?)";

            $searchValue = "%" . trim($search) . "%";

            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;

            $types .= "sss";
        }

        $whereSql = implode(" AND ", $where);
    
        // ---------------- Count Query ----------------

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM users u
            WHERE $whereSql
        ";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $countResult = $stmt->get_result();
        $totalBlockedUsers = (int)$countResult->fetch_assoc()["total"];

        $totalPages = max(1, (int)ceil($totalBlockedUsers / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        // ---------------- Table Query ----------------

        $query = "
            SELECT
                u.user_id,
                u.name,
                u.user_type,
                u.profile_url,
                u.blocked_at,
                bl.reason

            FROM users u

            LEFT JOIN (
                SELECT user_id, MAX(log_id) AS latest_log_id
                FROM blocked_logs
                GROUP BY user_id
            ) latest
                ON latest.user_id = u.user_id

            LEFT JOIN blocked_logs bl
                ON bl.log_id = latest.latest_log_id

            WHERE $whereSql

            ORDER BY u.blocked_at DESC

            LIMIT ? OFFSET ?
        ";

        $stmt = $conn->prepare($query);

        $tableParams = $params;
        $tableParams[] = $limit;
        $tableParams[] = $offset;

        $tableTypes = $types . "ii";

        $stmt->bind_param($tableTypes, ...$tableParams);

        $stmt->execute();

        $result = $stmt->get_result();

        $blockedUsers = [];

        while ($row = $result->fetch_assoc()) {
            $blockedUsers[] = $row;
        }

        return [
            "blockedUsers" => $blockedUsers,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $totalBlockedUsers,
                "totalPages" => $totalPages
            ]
        ];
    }

    // ========================== Patient Dashboard - Doctors Page ==============================

    public static function getPatientsDoctorsTableData(
    mysqli $conn,
    int $page,
    int $limit,
    string $search,
    array $filters
    ): array {

        $where = [
            "u.user_type = 'doctor'",
            "u.status = 'active'"
        ];

        $params = [];
        $types = "";

        // ---------------- Search ----------------

        if (!empty($search)) {

            $where[] = "(
                u.name LIKE ?
                OR u.email LIKE ?
                OR CAST(u.user_id AS CHAR) LIKE ?
                OR d.specialization LIKE ?
            )";

            $searchValue = "%" . trim($search) . "%";

            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;

            $types .= "ssss";
        }

        // ---------------- Consultation Mode ----------------

        // if (!empty($filters["consultation_mode"]) &&
        //     $filters["consultation_mode"] !== "all") {

        //     $where[] = "d.consultation_mode = ?";

        //     $params[] = $filters["consultation_mode"];
        //     $types .= "s";
        // }

        // ---------------- Availability ----------------

        if (!empty($filters["availability"]) &&
            $filters["availability"] !== "all") {

            switch ($filters["availability"]) {

                case "today":

                    $where[] = "
                        ds.slot_date = CURDATE()
                        AND ds.status = 'available'
                    ";

                    break;

                case "week":

                    $where[] = "
                        ds.slot_date BETWEEN CURDATE()
                        AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)
                        AND ds.status = 'available'
                    ";

                    break;

                case "month":

                    $where[] = "
                        ds.slot_date >= CURDATE()
                        AND MONTH(ds.slot_date) = MONTH(CURDATE())
                        AND YEAR(ds.slot_date) = YEAR(CURDATE())
                        AND ds.status = 'available'
                    ";

                    break;
            }
        }

        $whereSql = implode(" AND ", $where);

        // ---------------- Count Query ----------------

        $countQuery = "
            SELECT COUNT(DISTINCT u.user_id) AS total

            FROM users u

            INNER JOIN doctors d
                ON d.user_id = u.user_id

            LEFT JOIN doctor_slots ds
                ON ds.doctor_id = u.user_id

            WHERE $whereSql
        ";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $totalDoctors = (int)$stmt->get_result()->fetch_assoc()["total"];

        $totalPages = max(1, (int)ceil($totalDoctors / $limit));

        $page = max(1, min($page, $totalPages));

        $offset = ($page - 1) * $limit;

        // ---------------- Doctors Query ----------------

        $query = "
            SELECT DISTINCT

            u.user_id,
            u.name,
            u.profile_url,
            u.gender,

            d.specialization,
            d.experience,
            d.consultation_fee,
            d.hospital_name,
            d.rating,
            d.total_reviews,

            ds.slot_date,
            ds.start_time,
            ds.end_time

        FROM users u

        INNER JOIN doctors d
            ON d.user_id = u.user_id

        LEFT JOIN doctor_slots ds
            ON ds.doctor_id = u.user_id
            AND ds.status = 'available'

        WHERE $whereSql

        ORDER BY
            d.rating DESC,
            u.name ASC

        LIMIT ? OFFSET ?
        ";

        $stmt = $conn->prepare($query);

        $tableParams = $params;
        $tableParams[] = $limit;
        $tableParams[] = $offset;

        $tableTypes = $types . "ii";

        $stmt->bind_param($tableTypes, ...$tableParams);

        $stmt->execute();

        $result = $stmt->get_result();

        $doctors = [];

        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }

        return [
            "doctors" => $doctors,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $totalDoctors,
                "totalPages" => $totalPages
            ]
        ];
    }
    
}

?>