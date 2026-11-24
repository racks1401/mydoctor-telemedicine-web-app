<?php

require_once __DIR__ . '/../helpers/appointment.php';
require_once __DIR__ . '/../../config/firebase.php';

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
        $stats = $result ? $result->fetch_assoc() : [];

        $totalToday = (int) ($stats['total_today'] ?? 0);
        $confirmedToday = (int) ($stats['confirmed'] ?? 0);
        $pendingToday = (int) ($stats['pending'] ?? 0);
        $cancelledToday = (int) ($stats['cancelled'] ?? 0);

        $confirmedPct = $totalToday > 0 ? round(($confirmedToday / $totalToday) * 100) : 0;
        $pendingPct = $totalToday > 0 ? round(($pendingToday / $totalToday) * 100) : 0;
        $cancelledPct = $totalToday > 0 ? round(($cancelledToday / $totalToday) * 100) : 0;

        $yesterdayQuery = "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE() - INTERVAL 1 DAY";
        $yesterdayResult = $conn->query($yesterdayQuery);
        $yesterdayRow = $yesterdayResult ? $yesterdayResult->fetch_assoc() : [];
        $yesterdayCount = (int) ($yesterdayRow['total'] ?? 0);

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
        mysqli $conn, int $page = 1, int $limit = 10, string $search = "", array $filters = []
        ): array {

        $where = ["1=1"];
        $params = [];
        $types = "";

        if(!empty($filters["status"]) && $filters["status"] !="all"){
            $where[] = "a.status = ?";
            $params[] = $filters["status"];
            $types .= "s";
        }

        if(!empty($filters["appointment_mode"]) && $filters["appointment_mode"] != "all"){
            $where[] = "a.appointment_mode = ?";
            $params[] = $filters["appointment_mode"];
            $types .= "s";
        }

        if(!empty($filters["date_from"])){
            $where[] = "a.appointment_date >= ?";
            $params[] = $filters["date_from"];
            $types .= "s";
        }

        if(!empty($filters["date_to"])){
            $where[] = "a.appointment_date <= ?";
            $params[] = $filters["date_to"];
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
            a.appointment_duration,
            a.appointment_type,
            a.status,
            a.appointment_mode,
            a.follow_up_count,
            a.proposed_date,
            a.proposed_start_time,
            a.proposed_end_time,
            a.problem_description,
            p.user_id AS patient_id,
            p.name AS patient_name,
            d.user_id AS doctor_id,
            d.name AS doctor_name,
            d.profile_url AS profile_url,
            COALESCE(doc_det.specialization, 'General Physician') AS specialization
        FROM appointments a
        JOIN users p ON a.patient_id = p.user_id
        JOIN users d ON a.doctor_id = d.user_id
        LEFT JOIN doctors doc_det ON d.user_id = doc_det.user_id
        WHERE $whereSql
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
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

        $now = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
        foreach ($appointments as &$appt) {
            $times = getAppointmentSlotDateTimes($appt);
            $appt["slot_start"] = $times["start_str"];
            $appt["slot_end"] = $times["end_str"];
            $appt["chat_window_open"] = $times["chat_window_open"];
            $appt["chat_window_not_started"] = $times["chat_window_not_started"];
            $appt["chat_window_ended"] = $times["chat_window_ended"];

            // Follow-up eligibility (Section 5):
            // parent status "completed", now within 3 days after slot end, consultation, follow_up_count = 0
            $canFollowUp = false;
            if (
                $appt["status"] === "completed" 
                && $appt["appointment_type"] === "consultation" 
                && (int)($appt["follow_up_count"] ?? 0) === 0
            ) {
                $threeDaysAfter = (clone $times["end"])->modify("+3 days");
                if ($now <= $threeDaysAfter) {
                    $canFollowUp = true;
                }
            }
            $appt["can_follow_up"] = $canFollowUp;
        }
        unset($appt);

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
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                a.appointment_mode,
                a.appointment_duration,
                a.appointment_type,
                u.user_id AS doctor_id,
                u.name AS doctor_name,
                d.specialization AS doctor_specialization,
                u.profile_url AS doctor_profile_picture,
                c.conversation_id
            FROM appointments a
            JOIN users u
                ON a.doctor_id = u.user_id
            JOIN doctors d
                ON a.doctor_id = d.user_id
            LEFT JOIN conversations c
                ON c.appointment_id = a.appointment_id
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

        // Auto-complete any past confirmed appointments (No cron!)
        lazyCompleteBatch($conn, $userId);

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
            a.parent_appointment_id,
            a.follow_up_count,
            a.completed_at,
            a.proposed_date,
            a.proposed_start_time,
            a.proposed_end_time,
            a.problem_description,
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

        $now = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
        foreach ($appointments as &$appt) {
            $times = getAppointmentSlotDateTimes($appt);
            $appt["slot_start"] = $times["start_str"];
            $appt["slot_end"] = $times["end_str"];
            $appt["chat_window_open"] = $times["chat_window_open"];
            $appt["chat_window_not_started"] = $times["chat_window_not_started"];
            $appt["chat_window_ended"] = $times["chat_window_ended"];

            // Follow-up eligibility (Section 5):
            // parent status "completed", now within 3 days after slot end, consultation, follow_up_count = 0
            $canFollowUp = false;
            if (
                $appt["status"] === "completed" 
                && $appt["appointment_type"] === "consultation" 
                && (int)($appt["follow_up_count"] ?? 0) === 0
            ) {
                $threeDaysAfter = (clone $times["end"])->modify("+3 days");
                if ($now <= $threeDaysAfter) {
                    $canFollowUp = true;
                }
            }
            $appt["can_follow_up"] = $canFollowUp;
        }
        unset($appt);

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


    public static function bookAppointment(mysqli $conn, array $data): array {
        $doctorId = (int)($data['doctor_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);
        $appointmentDate = trim($data['appointment_date'] ?? '');
        $appointmentTime = trim($data['appointment_time'] ?? '');
        $appointmentMode = trim($data['appointment_mode'] ?? 'video_call');
        $appointmentType = trim($data['appointment_type'] ?? 'consultation');
        $duration = (int)($data['appointment_duration'] ?? 30);
        if ($duration <= 0) $duration = 30;

        $problemDescription = trim($data['problem_description'] ?? '');
        $notes = trim($data['notes'] ?? '');
        if (!empty($notes)) {
            $problemDescription = !empty($problemDescription)
                ? $problemDescription . "\n\nMedical Notes: " . $notes
                : "Medical Notes: " . $notes;
        }

        // Validate basic fields
        if ($doctorId <= 0) {
            throw new Exception("Invalid doctor selected.");
        }
        if ($patientId <= 0) {
            throw new Exception("Invalid patient identification.");
        }
        if (empty($appointmentDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointmentDate)) {
            throw new Exception("A valid appointment date is required.");
        }
        if (empty($appointmentTime)) {
            throw new Exception("A valid time slot is required.");
        }

        // Normalize time (HH:MM:SS)
        if (strlen($appointmentTime) === 5) {
            $appointmentTime .= ":00";
        }

        // Past date/time checks (Asia/Kolkata timezone safe)
        $todayStr = date('Y-m-d');
        if ($appointmentDate < $todayStr) {
            throw new Exception("Cannot book an appointment for a past date.");
        }
        if ($appointmentDate === $todayStr) {
            $slotTimestamp = strtotime("$appointmentDate $appointmentTime");
            if ($slotTimestamp <= time()) {
                throw new Exception("The selected time slot has already passed for today. Please select a future time slot.");
            }
        }

        // Validate enums
        $validModes = ['offline', 'chat', 'audio_call', 'video_call'];
        if (!in_array($appointmentMode, $validModes, true)) {
            $appointmentMode = 'video_call';
        }

        $validTypes = ['consultation', 'follow-up'];
        if (!in_array($appointmentType, $validTypes, true)) {
            $appointmentType = 'consultation';
        }

        $validPaymentMethods = ['cash', 'upi', 'card', 'net_banking', 'wallet'];
        $paymentMethod = trim($data['payment_method'] ?? 'upi');
        if (!in_array($paymentMethod, $validPaymentMethods, true)) {
            $paymentMethod = 'upi';
        }

        // Doctor verification & mode-specific fee check
        $docStmt = $conn->prepare("
            SELECT d.user_id, d.offline_fee, d.online_fee, d.available_mode, u.name AS doctor_name 
            FROM doctors d
            JOIN users u ON d.user_id = u.user_id
            WHERE d.user_id = ?
        ");
        $docStmt->bind_param("i", $doctorId);
        $docStmt->execute();
        $docRes = $docStmt->get_result();
        $docInfo = $docRes->fetch_assoc();
        $docStmt->close();

        if (!$docInfo) {
            throw new Exception("Doctor profile not found.");
        }

        // Verify doctor offers this mode
        $availModes = array_map('trim', explode(',', strtolower($docInfo['available_mode'] ?? 'video_call,chat,offline,audio_call')));
        if (!in_array($appointmentMode, $availModes, true)) {
            throw new Exception("Dr. " . $docInfo['doctor_name'] . " is not currently accepting " . str_replace('_', ' ', $appointmentMode) . " consultations.");
        }

        // Calculate accurate fee (offline_fee or online_fee)
        $charge = 0.00;
        if ($appointmentMode === 'offline') {
            $charge = (float)($docInfo['offline_fee'] ?? 150.00);
        } else {
            $charge = (float)($docInfo['online_fee'] ?? 100.00);
        }

        // Patient booking: always starts as 'pending'
        $status = 'pending';
        if ($paymentMethod === 'cash') {
            $paymentStatus = 'pending';
            $transactionId = null;
        } else {
            $paymentStatus = 'paid';
            $transactionId = 'TXN' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));
        }

        // Meeting link generation
        $meetingLink = null;
        if ($appointmentMode === 'video_call') {
            $meetingLink = "/my_doctor/public/patient/video-call.php?room=med_" . bin2hex(random_bytes(8));
        } elseif ($appointmentMode === 'audio_call') {
            $meetingLink = "/my_doctor/public/patient/audio-call.php?room=aud_" . bin2hex(random_bytes(8));
        } elseif ($appointmentMode === 'chat') {
            $meetingLink = "/my_doctor/public/patient/chat.php?doctor_id=" . $doctorId;
        }

        // ============================================
        // DATABASE TRANSACTION START
        // ============================================
        $conn->begin_transaction();

        try {
            // 1. Check double booking collision in appointments with row locking
            $collisionStmt = $conn->prepare("
                SELECT appointment_id 
                FROM appointments 
                WHERE doctor_id = ? 
                  AND appointment_date = ? 
                  AND appointment_time = ? 
                  AND status IN ('confirmed', 'pending') 
                FOR UPDATE
            ");
            $collisionStmt->bind_param("iss", $doctorId, $appointmentDate, $appointmentTime);
            $collisionStmt->execute();
            $collisionResult = $collisionStmt->get_result();
            if ($collisionResult->num_rows > 0) {
                $collisionStmt->close();
                throw new Exception("This time slot has just been booked by another patient. Please choose another slot.");
            }
            $collisionStmt->close();

            // 2. Check doctor availability exceptions for this date
            $exStmt = $conn->prepare("
                SELECT is_available, reason 
                FROM doctor_availability_exceptions 
                WHERE doctor_id = ? AND exception_date = ?
            ");
            $exStmt->bind_param("is", $doctorId, $appointmentDate);
            $exStmt->execute();
            $exRow = $exStmt->get_result()->fetch_assoc();
            $exStmt->close();

            if ($exRow && (int)$exRow['is_available'] === 0) {
                $leaveReason = !empty($exRow['reason']) ? $exRow['reason'] : "Doctor is on leave";
                throw new Exception("Dr. " . $docInfo['doctor_name'] . " is unavailable on " . $appointmentDate . ": " . $leaveReason);
            }

            // 3. Insert into appointments table
            $insQuery = "
                INSERT INTO appointments (
                    doctor_id,
                    patient_id,
                    appointment_date,
                    appointment_time,
                    appointment_duration,
                    appointment_type,
                    appointment_mode,
                    appointment_charge,
                    payment_status,
                    payment_method,
                    transaction_id,
                    status,
                    problem_description,
                    meeting_link
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $insStmt = $conn->prepare($insQuery);
            if (!$insStmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }

            $insStmt->bind_param(
                "iississdssssss",
                $doctorId,
                $patientId,
                $appointmentDate,
                $appointmentTime,
                $duration,
                $appointmentType,
                $appointmentMode,
                $charge,
                $paymentStatus,
                $paymentMethod,
                $transactionId,
                $status,
                $problemDescription,
                $meetingLink
            );

            if (!$insStmt->execute()) {
                throw new Exception("Failed to insert appointment: " . $insStmt->error);
            }

            $appointmentId = (int)$conn->insert_id;
            $insStmt->close();

            // 4. Update or Insert doctor_slots table
            $slotCheckStmt = $conn->prepare("
                SELECT slot_id, status 
                FROM doctor_slots 
                WHERE doctor_id = ? AND slot_date = ? AND start_time = ?
                FOR UPDATE
            ");
            $slotCheckStmt->bind_param("iss", $doctorId, $appointmentDate, $appointmentTime);
            $slotCheckStmt->execute();
            $slotRow = $slotCheckStmt->get_result()->fetch_assoc();
            $slotCheckStmt->close();

            if ($slotRow) {
                $updSlotStmt = $conn->prepare("
                    UPDATE doctor_slots 
                    SET status = 'booked', appointment_id = ? 
                    WHERE slot_id = ?
                ");
                $updSlotStmt->bind_param("ii", $appointmentId, $slotRow['slot_id']);
                if (!$updSlotStmt->execute()) {
                    throw new Exception("Failed to sync doctor slot: " . $updSlotStmt->error);
                }
                $updSlotStmt->close();
            } else {
                $endTime = date('H:i:s', strtotime("$appointmentTime + {$duration} minutes"));
                $newSlotStmt = $conn->prepare("
                    INSERT INTO doctor_slots (
                        doctor_id, slot_date, start_time, end_time, appointment_id, status
                    ) VALUES (?, ?, ?, ?, ?, 'booked')
                ");
                $newSlotStmt->bind_param("isssi", $doctorId, $appointmentDate, $appointmentTime, $endTime, $appointmentId);
                if (!$newSlotStmt->execute()) {
                    throw new Exception("Failed to allocate doctor slot: " . $newSlotStmt->error);
                }
                $newSlotStmt->close();
            }

            // 5. Update patient details in users table if provided
            $patientPhone = trim($data['patient_phone'] ?? '');
            $patientGender = trim($data['patient_gender'] ?? '');
            $patientDob = trim($data['patient_dob'] ?? '');

            if (!empty($patientPhone) || !empty($patientGender) || !empty($patientDob)) {
                $updPatientStmt = $conn->prepare("
                    UPDATE users SET 
                        phone = CASE WHEN ? != '' THEN ? ELSE phone END,
                        gender = CASE WHEN ? != '' THEN ? ELSE gender END,
                        dob = CASE WHEN ? != '' THEN ? ELSE dob END
                    WHERE user_id = ?
                ");
                $updPatientStmt->bind_param(
                    "ssssssi",
                    $patientPhone, $patientPhone,
                    $patientGender, $patientGender,
                    $patientDob, $patientDob,
                    $patientId
                );
                $updPatientStmt->execute();
                $updPatientStmt->close();
            }

            // Insert notification for doctor (Section 3)
            $patName = !empty($data['patient_name']) ? trim($data['patient_name']) : 'Patient #' . $patientId;
            createNotification(
                $conn,
                $doctorId,
                'new_appointment_request',
                $appointmentId,
                "New " . ($appointmentType === 'follow-up' ? 'follow-up' : 'consultation') . " request from {$patName} for {$appointmentDate} at {$appointmentTime}."
            );

            // 6. Commit transaction safely!
            $conn->commit();

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$patientId);
                pushDashboardAppointmentSignal((int)$doctorId);
            }

            return [
                "success"          => true,
                "appointment_id"   => $appointmentId,
                "status"           => $status,
                "payment_status"   => $paymentStatus,
                "payment_method"   => $paymentMethod,
                "transaction_id"   => $transactionId,
                "meeting_link"     => $meetingLink,
                "appointment_date" => $appointmentDate,
                "appointment_time" => $appointmentTime,
                "doctor_name"      => $docInfo['doctor_name'],
                "charge"           => $charge,
                "message"          => "Appointment successfully booked with Dr. " . $docInfo['doctor_name']
            ];

        } catch (Throwable $e) {
            // Rollback on any failure
            $conn->rollback();
            throw $e;
        }
    }


    /**
     * Fetch doctor's appointments table with search, filters, pagination, and lazy completion
     */
    

    /**
     * Books a free follow-up appointment (Section 5)
     * - Validates: parent status 'completed', within 3 days after slot end, type 'consultation', follow_up_count = 0
     * - Same doctor, mode copied from parent, free ($0.00)
     * - Status starts as 'pending', parent follow_up_count set to 1
     */
    public static function bookFollowUp(mysqli $conn, array $data): array {
        $parentApptId = (int)($data['parent_appointment_id'] ?? 0);
        $patientId = (int)($data['patient_id'] ?? 0);
        $appointmentDate = trim($data['appointment_date'] ?? '');
        $appointmentTime = trim($data['appointment_time'] ?? '');
        $problemDescription = trim($data['problem_description'] ?? '');

        if ($parentApptId <= 0) {
            throw new Exception("Invalid parent consultation ID.");
        }
        if ($patientId <= 0) {
            throw new Exception("Invalid patient identification.");
        }
        if (empty($appointmentDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointmentDate)) {
            throw new Exception("A valid appointment date is required.");
        }
        if (empty($appointmentTime)) {
            throw new Exception("A valid time slot is required.");
        }
        if (strlen($appointmentTime) === 5) {
            $appointmentTime .= ":00";
        }

        // Check past date/time (Asia/Kolkata)
        $todayStr = date('Y-m-d');
        if ($appointmentDate < $todayStr) {
            throw new Exception("Cannot book a follow-up for a past date.");
        }
        if ($appointmentDate === $todayStr) {
            $slotTimestamp = strtotime("$appointmentDate $appointmentTime");
            if ($slotTimestamp <= time()) {
                throw new Exception("The selected time slot has already passed for today. Please select a future time slot.");
            }
        }

        // 1. Fetch parent appointment with verification
        $parentStmt = $conn->prepare("
            SELECT appointment_id, doctor_id, patient_id, appointment_date,
                   appointment_time, appointment_duration, appointment_mode,
                   appointment_type, status, follow_up_count, completed_at
            FROM appointments
            WHERE appointment_id = ? AND patient_id = ?
        ");
        $parentStmt->bind_param("ii", $parentApptId, $patientId);
        $parentStmt->execute();
        $parent = $parentStmt->get_result()->fetch_assoc();
        $parentStmt->close();

        if (!$parent) {
            throw new Exception("Parent consultation appointment not found or unauthorized.");
        }

        // Lazy-complete check on parent
        if ($parent['status'] === 'confirmed') {
            if (lazyComplete($parent, $conn)) {
                $parent['status'] = 'completed';
            }
        }

        // Validate Section 5 Follow-Up conditions:
        if ($parent['status'] !== 'completed') {
            throw new Exception("Follow-up can only be booked for a completed consultation.");
        }

        if ($parent['appointment_type'] !== 'consultation') {
            throw new Exception("Follow-up appointments cannot have subsequent follow-ups.");
        }

        if ((int)$parent['follow_up_count'] > 0) {
            throw new Exception("A follow-up has already been booked for this consultation.");
        }

        $slotTimes = getAppointmentSlotDateTimes($parent);
        $threeDaysAfterEnd = (clone $slotTimes['end'])->modify('+3 days');
        if ($slotTimes['now'] > $threeDaysAfterEnd) {
            throw new Exception("The 3-day window for booking a free follow-up has expired.");
        }

        $doctorId = (int)$parent['doctor_id'];
        $appointmentMode = $parent['appointment_mode'];
        $duration = (int)($parent['appointment_duration'] ?? 30);
        if ($duration <= 0) $duration = 30;

        // Fetch doctor & patient names
        $docStmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
        $docStmt->bind_param("i", $doctorId);
        $docStmt->execute();
        $doctorName = ($docStmt->get_result()->fetch_assoc())['name'] ?? 'Doctor';
        $docStmt->close();

        $patStmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
        $patStmt->bind_param("i", $patientId);
        $patStmt->execute();
        $patientName = ($patStmt->get_result()->fetch_assoc())['name'] ?? 'Patient';
        $patStmt->close();

        // Meeting link for online follow-up
        $meetingLink = null;
        if ($appointmentMode === 'video_call') {
            $meetingLink = "/my_doctor/public/patient/video-call.php?room=med_" . bin2hex(random_bytes(8));
        } elseif ($appointmentMode === 'audio_call') {
            $meetingLink = "/my_doctor/public/patient/audio-call.php?room=aud_" . bin2hex(random_bytes(8));
        } elseif ($appointmentMode === 'chat') {
            $meetingLink = "/my_doctor/public/patient/chat.php?doctor_id=" . $doctorId;
        }

        // Transaction start
        $conn->begin_transaction();
        try {
            // 1. Collision check
            $colStmt = $conn->prepare("
                SELECT appointment_id
                FROM appointments
                WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status IN ('confirmed', 'pending')
                FOR UPDATE
            ");
            $colStmt->bind_param("iss", $doctorId, $appointmentDate, $appointmentTime);
            $colStmt->execute();
            if ($colStmt->get_result()->num_rows > 0) {
                $colStmt->close();
                throw new Exception("The selected time slot has already been booked. Please choose another slot.");
            }
            $colStmt->close();

            // 2. Doctor availability exception check
            $exStmt = $conn->prepare("
                SELECT is_available, reason
                FROM doctor_availability_exceptions
                WHERE doctor_id = ? AND exception_date = ?
            ");
            $exStmt->bind_param("is", $doctorId, $appointmentDate);
            $exStmt->execute();
            $exRow = $exStmt->get_result()->fetch_assoc();
            $exStmt->close();
            if ($exRow && (int)$exRow['is_available'] === 0) {
                $reason = !empty($exRow['reason']) ? $exRow['reason'] : "Doctor is unavailable";
                throw new Exception("Dr. {$doctorName} is unavailable on {$appointmentDate}: {$reason}");
            }

            // 3. Insert into appointments
            $insStmt = $conn->prepare("
                INSERT INTO appointments (
                    parent_appointment_id, doctor_id, patient_id,
                    appointment_date, appointment_time, appointment_duration,
                    appointment_type, follow_up_count, appointment_mode,
                    appointment_charge, payment_status, payment_method,
                    transaction_id, status, problem_description,
                    meeting_link, created_at
                ) VALUES (
                    ?, ?, ?,
                    ?, ?, ?,
                    'follow-up', 0, ?,
                    0.00, 'paid', 'wallet',
                    'FREE_FOLLOWUP', 'pending', ?,
                    ?, NOW()
                )
            ");
            $insStmt->bind_param(
                "iiississs",
                $parentApptId, $doctorId, $patientId,
                $appointmentDate, $appointmentTime, $duration,
                $appointmentMode, $problemDescription, $meetingLink
            );
            $insStmt->execute();
            $newApptId = (int)$conn->insert_id;
            $insStmt->close();

            // 4. Update parent appointment follow_up_count = 1
            $updParent = $conn->prepare("
                UPDATE appointments
                SET follow_up_count = 1
                WHERE appointment_id = ?
            ");
            $updParent->bind_param("i", $parentApptId);
            $updParent->execute();
            $updParent->close();

            // 5. Allocate doctor_slots
            $slotCheckStmt = $conn->prepare("
                SELECT slot_id FROM doctor_slots 
                WHERE doctor_id = ? AND slot_date = ? AND start_time = ?
            ");
            $slotCheckStmt->bind_param("iss", $doctorId, $appointmentDate, $appointmentTime);
            $slotCheckStmt->execute();
            $slotRow = $slotCheckStmt->get_result()->fetch_assoc();
            $slotCheckStmt->close();

            if ($slotRow) {
                $updSlotStmt = $conn->prepare("
                    UPDATE doctor_slots 
                    SET status = 'booked', appointment_id = ? 
                    WHERE slot_id = ?
                ");
                $updSlotStmt->bind_param("ii", $newApptId, $slotRow['slot_id']);
                $updSlotStmt->execute();
                $updSlotStmt->close();
            } else {
                $endTime = date('H:i:s', strtotime("$appointmentTime + {$duration} minutes"));
                $newSlotStmt = $conn->prepare("
                    INSERT INTO doctor_slots (
                        doctor_id, slot_date, start_time, end_time, appointment_id, status
                    ) VALUES (?, ?, ?, ?, ?, 'booked')
                ");
                $newSlotStmt->bind_param("isssi", $doctorId, $appointmentDate, $appointmentTime, $endTime, $newApptId);
                $newSlotStmt->execute();
                $newSlotStmt->close();
            }

            // 6. Notify Doctor
            createNotification(
                $conn,
                $doctorId,
                'new_appointment_request',
                $newApptId,
                "New Free Follow-up Request from {$patientName} for {$appointmentDate} at " . substr($appointmentTime, 0, 5) . "."
            );

            // 7. Notify Patient
            createNotification(
                $conn,
                $patientId,
                'new_appointment_request',
                $newApptId,
                "Your free follow-up appointment request with Dr. {$doctorName} has been submitted (pending confirmation)."
            );

            $conn->commit();

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$patientId);
                pushDashboardAppointmentSignal((int)$doctorId);
            }

            return [
                "success"               => true,
                "appointment_id"        => $newApptId,
                "parent_appointment_id" => $parentApptId,
                "doctor_id"             => $doctorId,
                "doctor_name"           => $doctorName,
                "appointment_date"      => $appointmentDate,
                "appointment_time"      => $appointmentTime,
                "appointment_mode"      => $appointmentMode,
                "appointment_type"      => "follow-up",
                "status"                => "pending",
                "message"               => "Free follow-up booked successfully! Awaiting Dr. {$doctorName}'s confirmation."
            ];

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public static function getDoctorAppointmentsTableData(
        mysqli $conn, int $page = 1, int $limit = 10, int $doctorId = 0, string $search = "", array $filters = []
    ): array {
        // Auto-complete any past confirmed appointments for this doctor
        lazyCompleteBatch($conn, $doctorId);

        $where = ["a.doctor_id = ?"];
        $params = [$doctorId];
        $types = "i";

        if (!empty($filters["status"]) && $filters["status"] !== "all") {
            if ($filters["status"] === "upcoming") {
                $where[] = "a.status IN ('pending', 'confirmed', 'resheduled') AND a.appointment_date >= CURDATE()";
            } else {
                $where[] = "a.status = ?";
                $params[] = $filters["status"];
                $types .= "s";
            }
        }

        if (!empty($filters["date"])) {
            $where[] = "a.appointment_date = ?";
            $params[] = $filters["date"];
            $types .= "s";
        }

        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.phone LIKE ? OR p.email LIKE ? OR CAST(a.appointment_id AS CHAR) LIKE ?)";
            $searchWild = "%" . trim($search) . "%";
            $params[] = $searchWild;
            $params[] = $searchWild;
            $params[] = $searchWild;
            $params[] = $searchWild;
            $types .= "ssss";
        }

        $whereSql = implode(" AND ", $where);

        $countQuery = "
            SELECT COUNT(*) AS total
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            WHERE $whereSql
        ";
        $stmt = $conn->prepare($countQuery);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = (int)$stmt->get_result()->fetch_assoc()["total"];
        $stmt->close();

        $totalPages = max(1, (int)ceil($total / $limit));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT 
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                a.appointment_duration,
                a.appointment_type,
                a.appointment_mode,
                a.appointment_charge,
                a.payment_status,
                a.payment_method,
                a.status,
                a.proposed_date,
                a.proposed_start_time,
                a.proposed_end_time,
                a.problem_description,
                a.meeting_link,
                a.parent_appointment_id,
                a.follow_up_count,
                a.completed_at,
                a.created_at,
                p.user_id AS patient_id,
                p.name AS patient_name,
                p.email AS patient_email,
                p.phone AS patient_phone,
                p.gender AS patient_gender,
                p.profile_url AS patient_profile_url
            FROM appointments a
            JOIN users p ON a.patient_id = p.user_id
            WHERE $whereSql
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($appointments as &$appt) {
            $times = getAppointmentSlotDateTimes($appt);
            $appt["slot_start"] = $times["start_str"];
            $appt["slot_end"] = $times["end_str"];
            $appt["chat_window_open"] = $times["chat_window_open"];
            $appt["chat_window_not_started"] = $times["chat_window_not_started"];
            $appt["chat_window_ended"] = $times["chat_window_ended"];
        }
        unset($appt);

        return [
            "appointments" => $appointments,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $total,
                "totalPages" => $totalPages
            ]
        ];
    }

    /**
     * Doctor confirms an appointment
     * - Status -> 'confirmed'
     * - Notification for patient (online: join 5m before; offline: clinic visit)
     * - Online: creates conversation row
     */
    public static function confirmAppointmentByDoctor(mysqli $conn, int $appointmentId, int $doctorId): array {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT a.*, d.name AS doctor_name, p.name AS patient_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.user_id
                JOIN users p ON a.patient_id = p.user_id
                WHERE a.appointment_id = ? AND a.doctor_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("ii", $appointmentId, $doctorId);
            $stmt->execute();
            $appt = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$appt) {
                throw new Exception("Appointment not found or not assigned to you.");
            }

            if (!in_array($appt["status"], ["pending", "resheduled"], true)) {
                throw new Exception("Only pending or rescheduled appointments can be confirmed. Current status: " . $appt["status"]);
            }

            // Update status to confirmed
            $upd = $conn->prepare("
                UPDATE appointments 
                SET status = 'confirmed' 
                WHERE appointment_id = ?
            ");
            $upd->bind_param("i", $appointmentId);
            $upd->execute();
            $upd->close();

            // Sync doctor slot
            $slotUpd = $conn->prepare("UPDATE doctor_slots SET status = 'booked' WHERE appointment_id = ?");
            $slotUpd->bind_param("i", $appointmentId);
            $slotUpd->execute();
            $slotUpd->close();

            $isOnline = in_array($appt["appointment_mode"], ["video_call", "chat", "audio_call"], true);
            $convId = null;

            if ($isOnline) {
                // Online appointment: check if conversation already exists between doctor and patient
                $chkStmt = $conn->prepare("SELECT conversation_id FROM conversations WHERE doctor_id = ? AND patient_id = ? LIMIT 1");
                $chkStmt->bind_param("ii", $doctorId, $appt["patient_id"]);
                $chkStmt->execute();
                $chkRes = $chkStmt->get_result()->fetch_assoc();
                $chkStmt->close();

                if ($chkRes) {
                    $convId = (int)$chkRes['conversation_id'];
                    $updConv = $conn->prepare("UPDATE conversations SET appointment_id = ? WHERE conversation_id = ?");
                    $updConv->bind_param("ii", $appointmentId, $convId);
                    $updConv->execute();
                    $updConv->close();
                } else {
                    $convStmt = $conn->prepare("
                        INSERT INTO conversations (appointment_id, doctor_id, patient_id, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    $convStmt->bind_param("iii", $appointmentId, $doctorId, $appt["patient_id"]);
                    $convStmt->execute();
                    $convId = (int)$conn->insert_id;
                    $convStmt->close();
                }

                // Patient notification: join 5 minutes before
                $notifMsg = "Your online consultation with Dr. {$appt['doctor_name']} on {$appt['appointment_date']} at {$appt['appointment_time']} is confirmed. Please join 5 minutes before the slot.";
            } else {
                // Offline appointment: no conversation
                $notifMsg = "Your in-person clinic visit with Dr. {$appt['doctor_name']} on {$appt['appointment_date']} at {$appt['appointment_time']} has been confirmed.";
            }

            createNotification($conn, (int)$appt["patient_id"], "appointment_confirmed", $appointmentId, $notifMsg);

            $conn->commit();

            if ($convId && function_exists('setConversationMembers')) {
                setConversationMembers((int)$convId, (int)$doctorId, (int)$appt["patient_id"]);
            }
            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$doctorId);
                pushDashboardAppointmentSignal((int)$appt["patient_id"]);
            }

            return [
                "success" => true,
                "appointment_id" => $appointmentId,
                "status" => "confirmed",
                "conversation_id" => $convId,
                "message" => "Appointment confirmed successfully."
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Doctor cancels an appointment
     * - Status -> 'cancelled'
     * - Frees doctor slot
     * - If follow-up within parent 3-day window, resets parent follow_up_count to 0
     * - Notification to patient
     */
    public static function cancelAppointmentByDoctor(mysqli $conn, int $appointmentId, int $doctorId, string $reason = ""): array {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT a.*, d.name AS doctor_name, p.name AS patient_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.user_id
                JOIN users p ON a.patient_id = p.user_id
                WHERE a.appointment_id = ? AND a.doctor_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("ii", $appointmentId, $doctorId);
            $stmt->execute();
            $appt = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$appt) {
                throw new Exception("Appointment not found or not assigned to you.");
            }

            if ($appt["status"] === "cancelled") {
                throw new Exception("Appointment is already cancelled.");
            }
            if ($appt["status"] === "completed") {
                throw new Exception("Completed appointments cannot be cancelled.");
            }

            $upd = $conn->prepare("
                UPDATE appointments 
                SET status = 'cancelled', cancelled_by = 'doctor', cancellation_reason = ? 
                WHERE appointment_id = ?
            ");
            $upd->bind_param("si", $reason, $appointmentId);
            $upd->execute();
            $upd->close();

            // Release doctor_slots
            $delSlot = $conn->prepare("DELETE FROM doctor_slots WHERE appointment_id = ?");
            $delSlot->bind_param("i", $appointmentId);
            $delSlot->execute();
            $delSlot->close();

            // Special rule (Section 5): If doctor cancels a follow-up and parent's 3-day window is still open, reset parent follow_up_count to 0
            if ($appt["appointment_type"] === "follow-up" && !empty($appt["parent_appointment_id"])) {
                $parentId = (int)$appt["parent_appointment_id"];
                $parentStmt = $conn->prepare("
                    SELECT appointment_date, appointment_time, appointment_duration, completed_at 
                    FROM appointments 
                    WHERE appointment_id = ?
                ");
                $parentStmt->bind_param("i", $parentId);
                $parentStmt->execute();
                $parentAppt = $parentStmt->get_result()->fetch_assoc();
                $parentStmt->close();

                if ($parentAppt) {
                    $parentTimes = getAppointmentSlotDateTimes($parentAppt);
                    $parentThreeDays = (clone $parentTimes["end"])->modify("+3 days");
                    $now = new DateTime("now", new DateTimeZone("Asia/Kolkata"));

                    if ($now <= $parentThreeDays) {
                        $resetParent = $conn->prepare("UPDATE appointments SET follow_up_count = 0 WHERE appointment_id = ?");
                        $resetParent->bind_param("i", $parentId);
                        $resetParent->execute();
                        $resetParent->close();
                    }
                }
            }

            // Notification to patient
            $reasonText = !empty($reason) ? " Reason: " . $reason : "";
            createNotification(
                $conn,
                (int)$appt["patient_id"],
                "appointment_cancelled",
                $appointmentId,
                "Dr. {$appt['doctor_name']} has cancelled your appointment for {$appt['appointment_date']} at {$appt['appointment_time']}.{$reasonText}"
            );

            $conn->commit();

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$doctorId);
                pushDashboardAppointmentSignal((int)$appt["patient_id"]);
            }

            return [
                "success" => true,
                "appointment_id" => $appointmentId,
                "status" => "cancelled",
                "message" => "Appointment cancelled successfully."
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Doctor reschedules an appointment
     * - Status -> 'resheduled'
     * - New slot saved in proposed_date, proposed_start_time, proposed_end_time
     * - Notification to patient
     */
    public static function rescheduleAppointmentByDoctor(
        mysqli $conn, int $appointmentId, int $doctorId, string $proposedDate, string $proposedStartTime, ?string $proposedEndTime = null
    ): array {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT a.*, d.name AS doctor_name, p.name AS patient_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.user_id
                JOIN users p ON a.patient_id = p.user_id
                WHERE a.appointment_id = ? AND a.doctor_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("ii", $appointmentId, $doctorId);
            $stmt->execute();
            $appt = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$appt) {
                throw new Exception("Appointment not found or not assigned to you.");
            }

            if (in_array($appt["status"], ["completed", "cancelled"], true)) {
                throw new Exception("Cannot reschedule a {$appt['status']} appointment.");
            }

            // Validate proposed date & time
            $today = date("Y-m-d");
            if ($proposedDate < $today) {
                throw new Exception("Proposed date cannot be in the past.");
            }
            if (strlen($proposedStartTime) === 5) {
                $proposedStartTime .= ":00";
            }
            if ($proposedDate === $today && strtotime("$proposedDate $proposedStartTime") <= time()) {
                throw new Exception("Proposed time slot has already passed for today.");
            }

            $duration = (int)($appt["appointment_duration"] ?? 30);
            if (empty($proposedEndTime)) {
                $proposedEndTime = date("H:i:s", strtotime("$proposedStartTime + {$duration} minutes"));
            }

            // Check collision with doctor's existing confirmed/pending appointments
            $colStmt = $conn->prepare("
                SELECT appointment_id 
                FROM appointments 
                WHERE doctor_id = ? 
                  AND appointment_date = ? 
                  AND appointment_time = ? 
                  AND appointment_id != ? 
                  AND status IN ('confirmed', 'pending')
            ");
            $colStmt->bind_param("issi", $doctorId, $proposedDate, $proposedStartTime, $appointmentId);
            $colStmt->execute();
            if ($colStmt->get_result()->num_rows > 0) {
                $colStmt->close();
                throw new Exception("You already have an active appointment scheduled at that time.");
            }
            $colStmt->close();

            // Check doctor leave/exception
            $exStmt = $conn->prepare("SELECT is_available, reason FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date = ?");
            $exStmt->bind_param("is", $doctorId, $proposedDate);
            $exStmt->execute();
            $exRow = $exStmt->get_result()->fetch_assoc();
            $exStmt->close();
            if ($exRow && (int)$exRow["is_available"] === 0) {
                throw new Exception("You are marked as unavailable on {$proposedDate} ({$exRow['reason']}).");
            }

            // Update appointment status to 'resheduled' (exact spelling preserved)
            $upd = $conn->prepare("
                UPDATE appointments 
                SET status = 'resheduled', proposed_date = ?, proposed_start_time = ?, proposed_end_time = ?
                WHERE appointment_id = ?
            ");
            $upd->bind_param("sssi", $proposedDate, $proposedStartTime, $proposedEndTime, $appointmentId);
            $upd->execute();
            $upd->close();

            // Notification for patient
            createNotification(
                $conn,
                (int)$appt["patient_id"],
                "appointment_rescheduled",
                $appointmentId,
                "Dr. {$appt['doctor_name']} proposed to reschedule your appointment to {$proposedDate} at {$proposedStartTime}. Please review and accept or pick an alternate slot."
            );

            $conn->commit();

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$doctorId);
                pushDashboardAppointmentSignal((int)$appt["patient_id"]);
            }

            return [
                "success" => true,
                "appointment_id" => $appointmentId,
                "status" => "resheduled",
                "proposed_date" => $proposedDate,
                "proposed_start_time" => $proposedStartTime,
                "proposed_end_time" => $proposedEndTime,
                "message" => "Appointment marked as rescheduled. Awaiting patient confirmation."
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Patient accepts proposed rescheduled slot:
     * - Re-validates slot is free
     * - Copies proposed_* into appointment_date & appointment_time
     * - Clears proposed_*
     * - Status -> 'confirmed'
     * - If online, ensures conversation row exists
     * - Notification to doctor
     */
    public static function acceptRescheduleByPatient(mysqli $conn, int $appointmentId, int $patientId): array {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT a.*, d.name AS doctor_name, p.name AS patient_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.user_id
                JOIN users p ON a.patient_id = p.user_id
                WHERE a.appointment_id = ? AND a.patient_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("ii", $appointmentId, $patientId);
            $stmt->execute();
            $appt = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$appt) {
                throw new Exception("Appointment not found or does not belong to you.");
            }

            if ($appt["status"] !== "resheduled") {
                throw new Exception("This appointment is not in rescheduled state.");
            }

            $proposedDate = $appt["proposed_date"];
            $proposedTime = $appt["proposed_start_time"];

            if (empty($proposedDate) || empty($proposedTime)) {
                throw new Exception("No proposed slot found on this appointment.");
            }

            // Re-validate that proposed slot is still free
            $today = date("Y-m-d");
            if ($proposedDate < $today || ($proposedDate === $today && strtotime("$proposedDate $proposedTime") <= time())) {
                throw new Exception("The proposed slot time has already passed. Please choose an alternate slot.");
            }

            $colStmt = $conn->prepare("
                SELECT appointment_id 
                FROM appointments 
                WHERE doctor_id = ? 
                  AND appointment_date = ? 
                  AND appointment_time = ? 
                  AND appointment_id != ? 
                  AND status IN ('confirmed', 'pending')
            ");
            $colStmt->bind_param("issi", $appt["doctor_id"], $proposedDate, $proposedTime, $appointmentId);
            $colStmt->execute();
            if ($colStmt->get_result()->num_rows > 0) {
                $colStmt->close();
                throw new Exception("The proposed slot was booked by another patient in the interim. Please pick an alternate slot.");
            }
            $colStmt->close();

            $duration = (int)($appt["appointment_duration"] ?? 30);
            $newEndTime = date("H:i:s", strtotime("$proposedTime + {$duration} minutes"));

            // Move slot in doctor_slots
            $updSlot = $conn->prepare("
                UPDATE doctor_slots 
                SET slot_date = ?, start_time = ?, end_time = ?, status = 'booked' 
                WHERE appointment_id = ?
            ");
            $updSlot->bind_param("sssi", $proposedDate, $proposedTime, $newEndTime, $appointmentId);
            $updSlot->execute();
            $updSlot->close();

            // Copy proposed_* into real slot, set confirmed, clear proposed_*
            $updAppt = $conn->prepare("
                UPDATE appointments 
                SET appointment_date = proposed_date, 
                    appointment_time = proposed_start_time, 
                    status = 'confirmed', 
                    proposed_date = NULL, 
                    proposed_start_time = NULL, 
                    proposed_end_time = NULL 
                WHERE appointment_id = ?
            ");
            $updAppt->bind_param("i", $appointmentId);
            $updAppt->execute();
            $updAppt->close();

            // If online, ensure conversation exists
            $isOnline = in_array($appt["appointment_mode"], ["video_call", "chat", "audio_call"], true);
            if ($isOnline) {
                $chkStmt = $conn->prepare("SELECT conversation_id FROM conversations WHERE doctor_id = ? AND patient_id = ? LIMIT 1");
                $chkStmt->bind_param("ii", $appt["doctor_id"], $patientId);
                $chkStmt->execute();
                $chkRes = $chkStmt->get_result()->fetch_assoc();
                $chkStmt->close();

                if ($chkRes) {
                    $updConv = $conn->prepare("UPDATE conversations SET appointment_id = ? WHERE conversation_id = ?");
                    $updConv->bind_param("ii", $appointmentId, $chkRes['conversation_id']);
                    $updConv->execute();
                    $updConv->close();
                } else {
                    $convStmt = $conn->prepare("
                        INSERT INTO conversations (appointment_id, doctor_id, patient_id, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    $convStmt->bind_param("iii", $appointmentId, $appt["doctor_id"], $patientId);
                    $convStmt->execute();
                    $convStmt->close();
                }
            }

            // Notification for doctor
            createNotification(
                $conn,
                (int)$appt["doctor_id"],
                "reschedule_accepted",
                $appointmentId,
                "Patient {$appt['patient_name']} accepted your proposed slot on {$proposedDate} at {$proposedTime}. Appointment is now confirmed."
            );

            $conn->commit();

            if ($isOnline && function_exists('setConversationMembers')) {
                $cStmt = $conn->prepare("SELECT conversation_id FROM conversations WHERE appointment_id = ?");
                if ($cStmt) {
                    $cStmt->bind_param("i", $appointmentId);
                    $cStmt->execute();
                    $cRow = $cStmt->get_result()->fetch_assoc();
                    $cStmt->close();
                    if ($cRow && !empty($cRow['conversation_id'])) {
                        setConversationMembers((int)$cRow['conversation_id'], (int)$appt["doctor_id"], (int)$patientId);
                    }
                }
            }

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$appt["doctor_id"]);
                pushDashboardAppointmentSignal((int)$patientId);
            }

            return [
                "success" => true,
                "appointment_id" => $appointmentId,
                "status" => "confirmed",
                "appointment_date" => $proposedDate,
                "appointment_time" => $proposedTime,
                "message" => "Proposed slot accepted. Your appointment is confirmed."
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Patient picks another slot for rescheduled appointment
     * - Validated against availability, exceptions, and booked slots
     * - Status back to 'pending'
     * - Clears proposed_*
     * - Notification to doctor
     */
    public static function proposeAlternateSlotByPatient(
        mysqli $conn, int $appointmentId, int $patientId, string $newDate, string $newTime
    ): array {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT a.*, d.name AS doctor_name, p.name AS patient_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.user_id
                JOIN users p ON a.patient_id = p.user_id
                WHERE a.appointment_id = ? AND a.patient_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("ii", $appointmentId, $patientId);
            $stmt->execute();
            $appt = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$appt) {
                throw new Exception("Appointment not found or does not belong to you.");
            }

            if (!in_array($appt["status"], ["resheduled", "pending"], true)) {
                throw new Exception("Cannot change slot for an appointment with status: " . $appt["status"]);
            }

            $doctorId = (int)$appt["doctor_id"];

            // Server-side slot validation (Asia/Kolkata)
            $today = date("Y-m-d");
            if ($newDate < $today) {
                throw new Exception("Cannot select a past date.");
            }
            if (strlen($newTime) === 5) {
                $newTime .= ":00";
            }
            if ($newDate === $today && strtotime("$newDate $newTime") <= time()) {
                throw new Exception("The selected time slot has already passed for today.");
            }

            // Check doctor leave exception
            $exStmt = $conn->prepare("SELECT is_available, reason FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date = ?");
            $exStmt->bind_param("is", $doctorId, $newDate);
            $exStmt->execute();
            $exRow = $exStmt->get_result()->fetch_assoc();
            $exStmt->close();
            if ($exRow && (int)$exRow["is_available"] === 0) {
                throw new Exception("Dr. {$appt['doctor_name']} is unavailable on {$newDate}: " . ($exRow['reason'] ?: "Doctor on leave"));
            }

            // Check collision with other appointments
            $colStmt = $conn->prepare("
                SELECT appointment_id 
                FROM appointments 
                WHERE doctor_id = ? 
                  AND appointment_date = ? 
                  AND appointment_time = ? 
                  AND appointment_id != ? 
                  AND status IN ('confirmed', 'pending')
            ");
            $colStmt->bind_param("issi", $doctorId, $newDate, $newTime, $appointmentId);
            $colStmt->execute();
            if ($colStmt->get_result()->num_rows > 0) {
                $colStmt->close();
                throw new Exception("This time slot is already booked. Please choose another slot.");
            }
            $colStmt->close();

            $duration = (int)($appt["appointment_duration"] ?? 30);
            $newEndTime = date("H:i:s", strtotime("$newTime + {$duration} minutes"));

            // Update doctor_slots
            $updSlot = $conn->prepare("
                UPDATE doctor_slots 
                SET slot_date = ?, start_time = ?, end_time = ?, status = 'booked' 
                WHERE appointment_id = ?
            ");
            $updSlot->bind_param("sssi", $newDate, $newTime, $newEndTime, $appointmentId);
            $updSlot->execute();
            $updSlot->close();

            // Update appointment: new slot, status back to 'pending', clear proposed_*
            $updAppt = $conn->prepare("
                UPDATE appointments 
                SET appointment_date = ?, 
                    appointment_time = ?, 
                    status = 'pending', 
                    proposed_date = NULL, 
                    proposed_start_time = NULL, 
                    proposed_end_time = NULL 
                WHERE appointment_id = ?
            ");
            $updAppt->bind_param("ssi", $newDate, $newTime, $appointmentId);
            $updAppt->execute();
            $updAppt->close();

            // Notification for doctor
            createNotification(
                $conn,
                $doctorId,
                "alternate_slot_proposed",
                $appointmentId,
                "Patient {$appt['patient_name']} proposed an alternate slot on {$newDate} at {$newTime}. Please review and confirm or reschedule."
            );

            $conn->commit();

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$doctorId);
                pushDashboardAppointmentSignal((int)$patientId);
            }

            return [
                "success" => true,
                "appointment_id" => $appointmentId,
                "status" => "pending",
                "appointment_date" => $newDate,
                "appointment_time" => $newTime,
                "message" => "New slot submitted. Awaiting doctor confirmation."
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Patient cancels an appointment
     * - Status -> 'cancelled'
     * - Releases doctor_slots
     * - If follow-up, parent follow_up_count stays 1 (Section 5)
     * - Notification to doctor
     */
    public static function cancelAppointmentByPatient(mysqli $conn, int $appointmentId, int $patientId, string $reason = ""): array {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("
                SELECT a.*, d.name AS doctor_name, p.name AS patient_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.user_id
                JOIN users p ON a.patient_id = p.user_id
                WHERE a.appointment_id = ? AND a.patient_id = ?
                FOR UPDATE
            ");
            $stmt->bind_param("ii", $appointmentId, $patientId);
            $stmt->execute();
            $appt = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$appt) {
                throw new Exception("Appointment not found or does not belong to you.");
            }

            if ($appt["status"] === "cancelled") {
                throw new Exception("Appointment is already cancelled.");
            }
            if ($appt["status"] === "completed") {
                throw new Exception("Completed appointments cannot be cancelled.");
            }

            $upd = $conn->prepare("
                UPDATE appointments 
                SET status = 'cancelled', cancelled_by = 'patient', cancellation_reason = ? 
                WHERE appointment_id = ?
            ");
            $upd->bind_param("si", $reason, $appointmentId);
            $upd->execute();
            $upd->close();

            // Release doctor_slots
            $delSlot = $conn->prepare("DELETE FROM doctor_slots WHERE appointment_id = ?");
            $delSlot->bind_param("i", $appointmentId);
            $delSlot->execute();
            $delSlot->close();

            // Notification to doctor
            $reasonText = !empty($reason) ? " Reason: " . $reason : "";
            createNotification(
                $conn,
                (int)$appt["doctor_id"],
                "appointment_cancelled",
                $appointmentId,
                "Patient {$appt['patient_name']} has cancelled their appointment for {$appt['appointment_date']} at {$appt['appointment_time']}.{$reasonText}"
            );

            $conn->commit();

            if (function_exists('pushDashboardAppointmentSignal')) {
                pushDashboardAppointmentSignal((int)$appt["doctor_id"]);
                pushDashboardAppointmentSignal((int)$patientId);
            }
            return [
                "success" => true,
                "appointment_id" => $appointmentId,
                "status" => "cancelled",
                "message" => "Appointment cancelled successfully."
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Doctor manually marks an appointment as completed
     */
    public static function completeAppointmentByDoctor(mysqli $conn, int $appointmentId, int $doctorId): array {
        $stmt = $conn->prepare("
            SELECT a.*, d.name AS doctor_name, p.name AS patient_name 
            FROM appointments a
            JOIN users d ON a.doctor_id = d.user_id
            JOIN users p ON a.patient_id = p.user_id
            WHERE a.appointment_id = ? AND a.doctor_id = ?
        ");
        $stmt->bind_param("ii", $appointmentId, $doctorId);
        $stmt->execute();
        $appt = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$appt) {
            throw new Exception("Appointment not found or not assigned to you.");
        }
        if ($appt["status"] === "completed") {
            return ["success" => true, "message" => "Appointment is already completed."];
        }

        $nowStr = date("Y-m-d H:i:s");
        $upd = $conn->prepare("UPDATE appointments SET status = 'completed', completed_at = ? WHERE appointment_id = ?");
        $upd->bind_param("si", $nowStr, $appointmentId);
        $upd->execute();
        $upd->close();

        createNotification(
            $conn,
            (int)$appt["patient_id"],
            "appointment_completed",
            $appointmentId,
            "Your appointment with Dr. {$appt['doctor_name']} has been concluded and marked as completed."
        );

        if (function_exists('pushDashboardAppointmentSignal')) {
            pushDashboardAppointmentSignal((int)$doctorId);
            pushDashboardAppointmentSignal((int)$appt["patient_id"]);
        }

        return [
            "success" => true,
            "appointment_id" => $appointmentId,
            "status" => "completed",
            "message" => "Appointment marked as completed."
        ];
    }

}
