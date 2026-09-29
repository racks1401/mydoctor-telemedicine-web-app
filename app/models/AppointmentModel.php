<?php

class AppointmentModel {

    public static function countTodayAppointments(
    mysqli $conn
    ): int {

        try {

            $query = "
                SELECT COUNT(*) AS total
                FROM appointments
                WHERE DATE(appointment_date)=CURDATE()
            ";

            $result = $conn->query($query);

            if (!$result) {

                return 0;

            }

            return (int)
                $result->fetch_assoc()['total'];

        } catch (Exception $e) {

            return 0;

        }

    }


    public static function getRecentAppointments(mysqli $conn): array
    {
        $query = "
            SELECT
                a.appointment_date,
                a.appointment_time,
                a.status,
                p.user_id AS patient_id,
                p.name AS patient_name,
                d.user_id AS doctor_id,
                d.name AS doctor_name
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            JOIN users d ON a.doctor_id = d.user_id
            WHERE (
                a.appointment_date > CURDATE()
                OR (
                    a.appointment_date = CURDATE()
                    AND a.appointment_time >= CURTIME()
                )
            )
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT 3
        ";

        $result = $conn->query($query);

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function getAppointmentStats(mysqli $conn): array {
        $query = "
            SELECT
            COUNT(*) AS total_today,

            COUNT(CASE WHEN status = 'confirmed' THEN 1 END) AS confirmed,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS cancelled

        FROM appointments
        WHERE appointment_date = CURDATE()";

        $result = $conn->query($query);
        $stats = $result->fetch_assoc();

        $totalToday = (int) $stats['total_today'];
        $confirmedToday = (int) $stats['confirmed'];
        $pendingToday = (int) $stats['pending'];
        $cancelledToday = (int) $stats['cancelled'];

        $confirmedPct = $totalToday > 0 ? round(($confirmedToday / $totalToday) * 100) : 0;
        $pendingPct = $totalToday > 0 ? round(($pendingToday / $totalToday) * 100) : 0;
        $cancelledPct = $totalToday > 0 ? round(($cancelledToday / $totalToday) * 100) : 0;

        $yesterdayQuery = "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE() - INTERVAL 1 DAY";
        $yesterdayResult = $conn->query($yesterdayQuery);
        $yesterdayCount = (int) $yesterdayResult->fetch_assoc()['total'];

        $trendPct = $yesterdayCount > 0 ? round((($totalToday - $yesterdayCount) / $yesterdayCount) * 100) : 0;

        return [
            "total_today" => [
                "count" => $totalToday,
                "trend" => $trendPct,
            ],

            "confirmed_today" => [
                "count" => $confirmedToday,
                "percentage" => $confirmedPct
            ],

            "pending" => [
                "count" => $pendingToday,
                "percentage" => $pendingPct
            ],

            "cancelled" => [
                "count" => $cancelledToday,
                "percentage" => $cancelledPct
            ]
        ];
    }

    public static function getAdminAppointmentsTableData(
        mysqli $conn, int $page = 1, int $limit, string $search, array $filters
        ): array {

        $where = ["a.appointment_date = CURDATE()"];
        $params = [];
        $types = "";

        if(!empty($filters["status"]) && $filters["status"] !="all"){
            $where[] = "a.status = ?";
            $params[] = $filters["status"];
            $types .= "s";
        }

        if(!empty($search)){
            $where[] = "(
                p.name LIKE ?
                OR d.name LIKE ?
                OR CAST(p.user_id AS CHAR) LIKE ?
                OR CAST(d.user_id AS CHAR) LIKE ?
            )";
            $search = "%" . trim($search) . "%";

            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        $whereSql = implode(" AND ", $where);

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM appointments a
            JOIN users p
            ON a.patient_id = p.user_id

            JOIN users d
            ON a.doctor_id = d.user_id
            WHERE $whereSql
        ";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $countResult = $stmt->get_result();
        $totalAppointments = (int)$countResult->fetch_assoc()["total"];

        $totalPages = max(1, (int) ceil($totalAppointments / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $query = "
           SELECT
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.appointment_type,
            a.status,
            a.appointment_mode,
            p.profile_url,
            p.user_id AS patient_id,
            p.name AS patient_name,

            d.user_id AS doctor_id,
            d.name AS doctor_name

        FROM appointments a

        JOIN users p
        ON a.patient_id = p.user_id

        JOIN users d
        ON a.doctor_id = d.user_id
        WHERE $whereSql
        ORDER BY appointment_date DESC, appointment_time DESC
        LIMIT ?
        OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($query);

        $stmt->bind_param($types, ...$params);

        $stmt->execute();

        $result = $stmt->get_result();

        $appointments = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();


        return [
            "appointments" => $appointments,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $totalAppointments,
                "totalPages" => $totalPages
            ]
        ];
    }

    // =========================== Patient Dashboard Functions ========================

    public static function getTotalAppointments(mysqli $conn, int $patientId): int
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM appointments
            WHERE patient_id = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public static function getTotalCompletedAppointments(mysqli $conn, int $patientId): int
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM appointments
            WHERE patient_id = ?
            AND status = 'completed'
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public static function getTotalDoctorsConsulted(mysqli $conn, int $patientId): int
    {
        $query = "
            SELECT COUNT(DISTINCT doctor_id) AS total
            FROM appointments
            WHERE patient_id = ?
            AND status = 'completed'
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public static function getTotalPendingAppointments(mysqli $conn, int $patientId): int
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM appointments
            WHERE patient_id = ?
            AND status = 'pending'
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public static function getRecentPatientAppointment(
    mysqli $conn,
    int $patientId
    ): array {

        $query = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                a.appointment_mode,
                a.status,
                u.user_id AS doctor_id,
                u.name AS doctor_name
            FROM appointments a
            JOIN users u
                ON a.doctor_id = u.user_id
            WHERE a.patient_id = ?
            AND a.status = 'completed'
            ORDER BY
                a.appointment_date DESC,
                a.appointment_time DESC
            LIMIT 3
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getUpcomingPatientAppointment(
    mysqli $conn,
    int $patientId
    ): array {

        $query = "
            SELECT
                a.appointment_date,
                a.appointment_time,
                a.appointment_mode,
                a.appointment_duration,
                a.appointment_type,
                u.user_id AS doctor_id,
                u.name AS doctor_name,
                d.specialization AS doctor_specialization,
                u.profile_url AS doctor_profile_picture
            FROM appointments a
            JOIN users u
                ON a.doctor_id = u.user_id
            JOIN doctors d
                ON a.doctor_id = d.user_id
            WHERE a.patient_id = ?
            AND a.status = 'confirmed'
            AND (
                a.appointment_date > CURDATE()
                OR (
                    a.appointment_date = CURDATE()
                    AND a.appointment_time >= CURTIME()
                )
            )
            ORDER BY a.appointment_date ASC,
                    a.appointment_time ASC
            LIMIT 1
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?? [];
    }

    public static function getPatientsAppointmentsTableData(
        mysqli $conn, int $page = 1, int $limit, int $userId, string $search, array $filters
        ): array {

        $where = ["a.patient_id = ?"];
        $params = [$userId];
        $types = "i";

        if (!empty($filters["status"]) && $filters["status"] != "all") {

            if ($filters["status"] == "upcoming") {
                // $where[] = "a.status IN ('pending', 'confirmed')";
                $where[] = "TIMESTAMP(a.appointment_date, a.appointment_time) >= NOW()";

            } else {

                $where[] = "a.status = ?";
                $params[] = $filters["status"];
                $types .= "s";
            }
        }

        if(!empty($search)){
            $where[] = "(
                p.name LIKE ?
                OR d.name LIKE ?
                OR CAST(p.user_id AS CHAR) LIKE ?
                OR CAST(d.user_id AS CHAR) LIKE ?
            )";
            $search = "%" . trim($search) . "%";

            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        $whereSql = implode(" AND ", $where);

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM appointments a
            JOIN users p
            ON a.patient_id = p.user_id

            JOIN users d
            ON a.doctor_id = d.user_id
            WHERE $whereSql
        ";

        $stmt = $conn->prepare($countQuery);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        $countResult = $stmt->get_result();
        $totalAppointments = (int)$countResult->fetch_assoc()["total"];

        $totalPages = max(1, (int) ceil($totalAppointments / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $query = "
           SELECT
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.appointment_type,
            a.status,
            a.appointment_mode,
            a.appointment_duration,
            d.user_id AS doctor_id,
            d.name AS doctor_name,
            d.profile_url,
            dr.specialization

        FROM appointments a

        JOIN users p
        ON a.patient_id = p.user_id
        JOIN users d
        ON a.doctor_id = d.user_id
        JOIN doctors dr
        ON a.doctor_id = dr.user_id
        WHERE $whereSql
        ORDER BY appointment_date DESC, appointment_time DESC
        LIMIT ?
        OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($query);

        $stmt->bind_param($types, ...$params);

        $stmt->execute();

        $result = $stmt->get_result();

        $appointments = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();


        return [
            "appointments" => $appointments,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $totalAppointments,
                "totalPages" => $totalPages
            ]
        ];
    }

}