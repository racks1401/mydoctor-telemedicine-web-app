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

                COALESCE(NULLIF(d.specialization, ''), 'General Physician') AS specialization,
                COALESCE(NULLIF(d.department, ''), 'General Medicine') AS department,
                d.verification_status,

                COALESCE(
                    GROUP_CONCAT(
                        DISTINCT vd.document_type
                        ORDER BY vd.document_type
                        SEPARATOR ' + '
                    ),
                    'Medical License + Gov ID'
                ) AS document_types,

                COALESCE(MIN(vd.uploaded_at), u.created_at) AS submitted_at

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

        $doctorTrend = $totalBlocked > 0 ? ($doctorsBlocked / $totalBlocked) * 100 : 0;
        $patientTrend = $totalBlocked > 0 ? ($patientsBlocked / $totalBlocked) * 100 : 0;

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
                COALESCE(u.blocked_at, bl.created_at) AS blocked_at,
                COALESCE(bl.reason, 'Terms of service violation') AS reason

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
                OR d.department LIKE ?
            )";
            $searchValue = "%" . trim($search) . "%";
            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;
            $params[] = $searchValue;
            $types .= "sssss";
        }

        // ---------------- Availability ----------------
        if (!empty($filters["availability"]) && $filters["availability"] !== "all") {
            switch ($filters["availability"]) {
                case "today":
                    $where[] = "(
                        d.availability = 1
                        AND EXISTS (
                            SELECT 1 FROM doctor_availability da 
                            WHERE da.doctor_id = u.user_id 
                              AND da.day_of_week = DAYNAME(CURDATE())
                              AND da.is_available = 1
                        )
                        AND NOT EXISTS (
                            SELECT 1 FROM doctor_availability_exceptions de
                            WHERE de.doctor_id = u.user_id 
                              AND de.exception_date = CURDATE()
                              AND de.is_available = 0
                        )
                    )";
                    break;

                case "week":
                    $where[] = "(
                        d.availability = 1
                        AND EXISTS (
                            SELECT 1 FROM doctor_availability da 
                            WHERE da.doctor_id = u.user_id 
                              AND da.is_available = 1
                        )
                    )";
                    break;

                case "month":
                    $where[] = "d.availability = 1";
                    break;
            }
        }

        $whereSql = implode(" AND ", $where);

        // ---------------- Count Query ----------------
        $countQuery = "
            SELECT COUNT(DISTINCT u.user_id) AS total
            FROM users u
            INNER JOIN doctors d ON d.user_id = u.user_id
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
            SELECT
                u.user_id,
                u.name,
                u.email,
                u.phone,
                u.gender,
                u.city,
                u.state,
                u.profile_url,
                u.status,
                d.specialization,
                d.department,
                d.qualification,
                d.experience,
                d.working_time,
                d.about,
                d.availability,
                d.verification_status,
                d.rating,
                d.total_reviews,
                d.available_mode,
                d.offline_fee,
                d.online_fee,
                NULL AS next_slot_date,
                NULL AS next_start_time,
                NULL AS next_end_time
            FROM users u
            INNER JOIN doctors d ON d.user_id = u.user_id
            WHERE $whereSql
            ORDER BY u.name ASC
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
            $row['rating'] = number_format((float)($row['rating'] ?? 4.5), 1);
            $row['total_reviews'] = (int)($row['total_reviews'] ?? 25);
            $row['offline_fee'] = (float)($row['offline_fee'] ?? 150);
            $row['online_fee'] = (float)($row['online_fee'] ?? 100);
            $fees = array_filter([(float)$row['offline_fee'], (float)$row['online_fee']], function($f) { return $f > 0; });
            $row['min_fee'] = !empty($fees) ? min($fees) : 50;
            $row['consultation_fee'] = $row['online_fee'] ?: $row['offline_fee'];
            $row['available_mode'] = !empty($row['available_mode']) ? $row['available_mode'] : 'video_call';
            $row['hospital_name'] = !empty($row['city']) 
                ? "City Care Hospital, " . $row['city'] 
                : (!empty($row['department']) ? $row['department'] . " Medical Center" : "MediCentral General Hospital");

            if (!empty($row['next_slot_date'])) {
                $isToday = ($row['next_slot_date'] === date('Y-m-d'));
                $dateLabel = $isToday ? "Today" : date("M d", strtotime($row['next_slot_date']));
                $timeLabel = date("g:i A", strtotime($row['next_start_time'])) . " - " . date("g:i A", strtotime($row['next_end_time']));
                $row['availability_text'] = "Available " . $dateLabel . " (" . $timeLabel . ")";
            } elseif (!empty($row['working_time'])) {
                $row['availability_text'] = "Hours: " . $row['working_time'];
            } else {
                $row['availability_text'] = "Available by Appointment";
            }

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


    // =========================================================================
    // SCHEDULE EXCEPTIONS & DYNAMIC SLOT CALCULATION ENGINE
    // =========================================================================

    public static function generateSlotsFromRange(string $startTime, string $endTime, int $durationMinutes): array {
        $slots = [];
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        if ($start === false || $end === false || $start >= $end || $durationMinutes <= 0) {
            return [];
        }
        $step = $durationMinutes * 60;
        for ($t = $start; $t + $step <= $end; $t += $step) {
            $slots[] = [
                'start_time'   => date('H:i:s', $t),
                'end_time'     => date('H:i:s', $t + $step),
                'display_time' => date('g:i A', $t),
                'display_slot' => date('g:i A', $t) . ' - ' . date('g:i A', $t + $step)
            ];
        }
        return $slots;
    }

    public static function getDoctorBookingSlots(mysqli $conn, int $doctorId, string $date): array {
        // Step 1: Check exceptions for this doctor & date
        $stmt = $conn->prepare("SELECT is_available, start_time, end_time, slot_duration, reason FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date = ?");
        $stmt->bind_param("is", $doctorId, $date);
        $stmt->execute();
        $res = $stmt->get_result();

        $generatedSlots = [];
        $hasException = false;

        while ($row = $res->fetch_assoc()) {
            $hasException = true;
            if ((int)$row['is_available'] === 0) {
                return [
                    'is_blocked' => true,
                    'reason'     => !empty($row['reason']) ? $row['reason'] : 'Doctor is on leave',
                    'slots'      => []
                ];
            }
            $duration = (int)($row['slot_duration'] ?: 30);
            $sub = self::generateSlotsFromRange($row['start_time'], $row['end_time'], $duration);
            $generatedSlots = array_merge($generatedSlots, $sub);
        }

        // Step 2: Fallback to baseline weekly recurring doctor_availability
        if (!$hasException) {
            $dayOfWeek = date('l', strtotime($date));
            $stmt2 = $conn->prepare("SELECT start_time, end_time, slot_duration, is_available FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1");
            $stmt2->bind_param("is", $doctorId, $dayOfWeek);
            $stmt2->execute();
            $res2 = $stmt2->get_result();

            while ($row2 = $res2->fetch_assoc()) {
                $duration = (int)($row2['slot_duration'] ?: 30);
                $sub = self::generateSlotsFromRange($row2['start_time'], $row2['end_time'], $duration);
                $generatedSlots = array_merge($generatedSlots, $sub);
            }

            if (empty($generatedSlots)) {
                return [
                    'is_blocked' => true,
                    'reason'     => "Doctor is not available on {$dayOfWeek}s",
                    'slots'      => []
                ];
            }
        }

        // Step 3: Remove booked appointments
        $stmt3 = $conn->prepare("SELECT appointment_time, appointment_duration FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status IN ('confirmed', 'pending')");
        $stmt3->bind_param("is", $doctorId, $date);
        $stmt3->execute();
        $res3 = $stmt3->get_result();

        $bookedRanges = [];
        while ($b = $res3->fetch_assoc()) {
            $bStart = strtotime("$date " . $b['appointment_time']);
            $bDuration = (int)($b['appointment_duration'] ?: 30);
            $bEnd = $bStart + ($bDuration * 60);
            $bookedRanges[] = ['start' => $bStart, 'end' => $bEnd];
        }

        $finalSlots = [];
        $now = time();
        $isToday = ($date === date('Y-m-d'));

        foreach ($generatedSlots as $slot) {
            $slotStart = strtotime("$date " . $slot['start_time']);
            $slotEnd = strtotime("$date " . $slot['end_time']);

            // Filter out past time if date is today (only future slots allowed)
            if ($isToday && $slotStart <= $now) {
                continue;
            }

            $isBooked = false;
            foreach ($bookedRanges as $br) {
                if ($slotStart < $br['end'] && $slotEnd > $br['start']) {
                    $isBooked = true;
                    break;
                }
            }

            if (!$isBooked) {
                $finalSlots[] = $slot;
            }
        }

        return [
            'is_blocked'  => false,
            'reason'      => null,
            'slots'       => $finalSlots,
            'total_slots' => count($finalSlots)
        ];
    }

    public static function getDoctorMonthScheduleStatus(mysqli $conn, int $doctorId, int $daysAhead = 60): array {
        // 1. Get baseline active days of week
        $stmt = $conn->prepare("SELECT DISTINCT day_of_week FROM doctor_availability WHERE doctor_id = ? AND is_available = 1");
        $stmt->bind_param("i", $doctorId);
        $stmt->execute();
        $res = $stmt->get_result();
        $activeDays = [];
        while ($r = $res->fetch_assoc()) {
            $activeDays[] = $r['day_of_week'];
        }

        // 2. Get exceptions in the next $daysAhead range
        $stmtExc = $conn->prepare("SELECT exception_date, is_available, reason FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)");
        $stmtExc->bind_param("ii", $doctorId, $daysAhead);
        $stmtExc->execute();
        $resExc = $stmtExc->get_result();
        $exceptionsMap = [];
        while ($er = $resExc->fetch_assoc()) {
            $exceptionsMap[$er['exception_date']] = $er;
        }

        // 3. Build status for each date from today to today + $daysAhead
        $scheduleStatus = [];
        $today = new DateTime();
        for ($i = 0; $i < $daysAhead; $i++) {
            $curDate = (clone $today)->modify("+{$i} days")->format('Y-m-d');
            $dayOfWeek = (clone $today)->modify("+{$i} days")->format('l');

            if (isset($exceptionsMap[$curDate])) {
                $exc = $exceptionsMap[$curDate];
                if ((int)$exc['is_available'] === 0) {
                    $scheduleStatus[$curDate] = [
                        'status' => 'blocked',
                        'reason' => !empty($exc['reason']) ? $exc['reason'] : 'Doctor on leave'
                    ];
                } else {
                    $scheduleStatus[$curDate] = [
                        'status' => 'available',
                        'reason' => 'Custom timing'
                    ];
                }
            } else {
                if (in_array($dayOfWeek, $activeDays)) {
                    $scheduleStatus[$curDate] = [
                        'status' => 'available',
                        'reason' => 'Normal working hours'
                    ];
                } else {
                    $scheduleStatus[$curDate] = [
                        'status' => 'blocked',
                        'reason' => "Weekly off ({$dayOfWeek})"
                    ];
                }
            }
        }

        return $scheduleStatus;
    }

    public static function getDoctorBookingInfo(mysqli $conn, int $doctorId): ?array {
        $stmt = $conn->prepare("
            SELECT 
                u.user_id, u.name, u.email, u.phone, u.city, u.state, u.address, u.pin, u.profile_url, u.gender,
                d.medical_reg_no, d.specialization, d.department, d.qualification, d.experience, d.working_time,
                d.about, d.rating, d.total_reviews, d.available_mode, d.verification_status,
                d.offline_fee, d.online_fee
            FROM users u
            INNER JOIN doctors d ON d.user_id = u.user_id
            WHERE u.user_id = ? AND u.status = 'active'
        ");
        $stmt->bind_param("i", $doctorId);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();

        if (!$doctor) {
            return null;
        }

        $doctor['rating'] = number_format((float)($doctor['rating'] ?? 4.5), 1);
        $doctor['total_reviews'] = (int)($doctor['total_reviews'] ?? 25);
        $doctor['offline_fee'] = (float)($doctor['offline_fee'] ?? 150);
        $doctor['online_fee'] = (float)($doctor['online_fee'] ?? 100);
        $doctor['hospital_name'] = !empty($doctor['city']) 
            ? "City Care Hospital, " . $doctor['city'] 
            : (!empty($doctor['department']) ? $doctor['department'] . " Medical Center" : "MediCentral General Hospital");

        // Schedule status map for next 60 days
        $doctor['schedule_status'] = self::getDoctorMonthScheduleStatus($conn, $doctorId, 60);

        return $doctor;
    }

    public static function getRestrictionAuditLogs(mysqli $conn, int $limit = 10): array {
        $sql = "
            SELECT 
                bl.log_id,
                bl.user_id,
                bl.action,
                bl.reason,
                bl.notes,
                bl.created_at,
                u.name AS user_name,
                u.user_type,
                COALESCE(adm.name, 'Admin System') AS admin_name
            FROM blocked_logs bl
            JOIN users u ON bl.user_id = u.user_id
            LEFT JOIN users adm ON bl.performed_by = adm.user_id
            ORDER BY bl.created_at DESC
            LIMIT ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getRestrictionPolicies(mysqli $conn): array {
        $sql = "
            SELECT 
                policy_id,
                title,
                icon,
                description,
                status,
                display_order
            FROM restriction_policies
            WHERE status = 'active'
            ORDER BY display_order ASC, policy_id ASC
            LIMIT 4
        ";
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function addDoctor(mysqli $conn, array $data): array {
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $gender = $data['gender'] ?? 'male';
        $dob = !empty($data['dob']) ? $data['dob'] : null;
        $address = trim($data['address'] ?? '');
        $city = trim($data['city'] ?? '');
        $state = trim($data['state'] ?? '');
        $pin = trim($data['pin'] ?? '');

        // Doctor specific fields
        $medical_reg_no = trim($data['medical_reg_no'] ?? '');
        if (empty($medical_reg_no)) {
            $medical_reg_no = 'MED-' . rand(10000, 99999);
        }
        $specialization = trim($data['specialization'] ?? 'General Medicine');
        $department = trim($data['department'] ?? $specialization);
        $qualification = trim($data['qualification'] ?? 'MBBS');
        $experience = (int)($data['experience'] ?? 1);
        $working_time = trim($data['working_time'] ?? '09:00 AM - 05:00 PM');
        $about = trim($data['about'] ?? '');
        $online_fee = isset($data['online_fee']) ? (float)$data['online_fee'] : 100.00;
        $offline_fee = isset($data['offline_fee']) ? (float)$data['offline_fee'] : 150.00;
        $available_mode = trim($data['available_mode'] ?? 'video_call,chat,offline,audio_call');
        $availability = isset($data['availability']) ? (int)$data['availability'] : 1;
        $verification_status = 'verified';
        $verified_by = (int)($data['verified_by'] ?? 0) ?: null;

        if (empty($name)) {
            return ['success' => false, 'message' => 'Doctor name is required.'];
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Valid email address is required.'];
        }

        // Check if email already exists
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $chk->close();
            return ['success' => false, 'message' => 'A user with this email already exists.'];
        }
        $chk->close();

        // Hash password
        $passHash = password_hash(!empty($password) ? $password : 'Doctor@123', PASSWORD_DEFAULT);

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("
                INSERT INTO users 
                (user_type, name, email, password, phone, gender, dob, address, city, state, pin, status, privacy_accepted, privacy_accepted_at, created_at)
                VALUES ('doctor', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1, NOW(), NOW())
            ");
            $stmt->bind_param("ssssssssss", $name, $email, $passHash, $phone, $gender, $dob, $address, $city, $state, $pin);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();

            $docStmt = $conn->prepare("
                INSERT INTO doctors 
                (user_id, medical_reg_no, specialization, department, qualification, experience, working_time, about, online_fee, offline_fee, available_mode, availability, verification_status, verified_by, verified_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $docStmt->bind_param(
                "issssissddsisi",
                $userId,
                $medical_reg_no,
                $specialization,
                $department,
                $qualification,
                $experience,
                $working_time,
                $about,
                $online_fee,
                $offline_fee,
                $available_mode,
                $availability,
                $verification_status,
                $verified_by
            );
            $docStmt->execute();
            $docStmt->close();

            $conn->commit();
            return ['success' => true, 'message' => 'Doctor added successfully.', 'user_id' => $userId];
        } catch (Throwable $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Admin approves or rejects a doctor's verification.
     * Records which admin did it and when, and notifies the doctor.
     *
     * @param string $status 'verified' or 'rejected'
     */
    public static function updateVerificationStatus(
        mysqli $conn,
        int $doctorId,
        string $status,
        int $adminId,
        string $reason = ''
    ): array {
        if (!in_array($status, ['verified', 'rejected'], true)) {
            return ['success' => false, 'message' => 'Invalid verification status.'];
        }
        if ($doctorId <= 0) {
            return ['success' => false, 'message' => 'Valid doctor ID is required.'];
        }

        $stmt = $conn->prepare("
            UPDATE doctors d
            INNER JOIN users u ON u.user_id = d.user_id AND u.user_type = 'doctor'
            SET d.verification_status = ?, d.verified_by = ?, d.verified_at = NOW()
            WHERE d.user_id = ?
        ");
        $stmt->bind_param("sii", $status, $adminId, $doctorId);
        $stmt->execute();
        $found = $stmt->affected_rows;
        $stmt->close();

        if ($found <= 0) {
            // Either the doctor does not exist or already has this status
            $chk = $conn->prepare("SELECT verification_status FROM doctors WHERE user_id = ?");
            $chk->bind_param("i", $doctorId);
            $chk->execute();
            $row = $chk->get_result()->fetch_assoc();
            $chk->close();
            if (!$row) {
                return ['success' => false, 'message' => 'Doctor not found.'];
            }
        }

        $message = ($status === 'verified')
            ? 'Your doctor account has been verified by the admin. Patients can now book consultations with you.'
            : 'Your doctor verification was rejected by the admin.' . ($reason !== '' ? ' Reason: ' . $reason : '');
        $type = ($status === 'verified') ? 'verification_approved' : 'verification_rejected';

        $nStmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, appointment_id, message, is_read, created_at)
            VALUES (?, ?, NULL, ?, 0, NOW())
        ");
        $nStmt->bind_param("iss", $doctorId, $type, $message);
        $nStmt->execute();
        $nStmt->close();

        return [
            'success' => true,
            'message' => ($status === 'verified') ? 'Doctor verified successfully.' : 'Doctor verification rejected.'
        ];
    }
}
?>
