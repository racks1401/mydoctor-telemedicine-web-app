<?php
/**
 * Appointment Date/Time and Lifecycle Helpers
 * All calculations use server time in Asia/Kolkata. Never trust frontend timestamps.
 */

require_once __DIR__ . '/response.php';

date_default_timezone_set('Asia/Kolkata');

/**
 * ============================================================================
 * CHAT TIMING SWITCH (DEBUG / TESTING)
 * ============================================================================
 * Set to TRUE: Disables the "5 minutes before slot" restriction so chat
 *              can start and be tested anytime for confirmed appointments.
 * Set to FALSE: Strict production logic (chat only opens 5 minutes before
 *               the slot start and ends when the slot duration finishes).
 * ============================================================================
 */
if (!defined('DEBUG_DISABLE_CHAT_5MIN_RESTRICTION')) {
    define('DEBUG_DISABLE_CHAT_5MIN_RESTRICTION', true);
}

/**
 * Returns an appointment's slot start and end as full DateTime objects and strings
 * Also calculates chat window boundaries (slot start minus 5 minutes to slot end)
 *
 * @param array $appointment Needs appointment_date, appointment_time, appointment_duration
 * @return array
 */
function getAppointmentSlotDateTimes(array $appointment): array {
    $tz = new DateTimeZone('Asia/Kolkata');
    $date = trim($appointment['appointment_date'] ?? '');
    $time = trim($appointment['appointment_time'] ?? '');
    $duration = (int)($appointment['appointment_duration'] ?? 30);
    if ($duration <= 0) {
        $duration = 30;
    }

    if (strlen($time) === 5) {
        $time .= ':00';
    }

    $dateTimeStr = $date . ' ' . $time;
    $start = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeStr, $tz);
    if (!$start) {
        $start = new DateTime($dateTimeStr, $tz);
    }

    $end = (clone $start)->modify("+{$duration} minutes");
    $chatStart = (clone $start)->modify("-5 minutes");

    $now = new DateTime('now', $tz);

    // --- Chat Window Logic with Debug Switch ---
    if (DEBUG_DISABLE_CHAT_5MIN_RESTRICTION) {
        // [DEBUG MODE ACTIVE] Chat is allowed anytime immediately
        $chatWindowOpen = true;
        $chatWindowNotStarted = false;
        $chatWindowEnded = false;
    } else {
        // [PRODUCTION LOGIC] Chat opens 5 minutes before slot and ends when slot duration finishes
        $chatWindowOpen = ($now >= $chatStart && $now <= $end);
        $chatWindowNotStarted = ($now < $chatStart);
        $chatWindowEnded = ($now > $end);
    }

    return [
        'start'                 => $start,
        'end'                   => $end,
        'start_str'             => $start->format('Y-m-d H:i:s'),
        'end_str'               => $end->format('Y-m-d H:i:s'),
        'chat_start'            => $chatStart,
        'chat_start_str'        => $chatStart->format('Y-m-d H:i:s'),
        'now'                   => $now,
        'now_str'               => $now->format('Y-m-d H:i:s'),
        'is_started'            => ($now >= $start),
        'is_ended'              => ($now > $end),
        'chat_window_open'      => $chatWindowOpen,
        'chat_window_not_started' => $chatWindowNotStarted,
        'chat_window_ended'     => $chatWindowEnded,
        'minutes_until_chat'    => max(0, (int)ceil(($chatStart->getTimestamp() - $now->getTimestamp()) / 60))
    ];
}

/**
 * Inserts a notification record into notifications table
 *
 * @param mysqli $conn
 * @param int $userId
 * @param string $type
 * @param int|null $appointmentId
 * @param string $message
 * @return int Inserted notification ID
 */
function createNotification(mysqli $conn, int $userId, string $type, ?int $appointmentId, string $message): int {
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, type, appointment_id, message, is_read, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    $stmt->bind_param("isis", $userId, $type, $appointmentId, $message);
    $stmt->execute();
    $id = (int)$conn->insert_id;
    $stmt->close();
    return $id;
}

/**
 * Lazy-completes a confirmed appointment if its slot end has passed
 * - Sets status = 'completed'
 * - completed_at = slot end
 * - Inserts a patient notification
 *
 * @param array|int $appointment Array of appointment columns, or appointment ID
 * @param mysqli $conn
 * @return bool True if appointment was transitioned to completed, false otherwise
 */
function lazyComplete($appointment, mysqli $conn): bool {
    // If debug mode is active, do not auto-expire confirmed appointments so chat can be tested anytime
    if (defined('DEBUG_DISABLE_CHAT_5MIN_RESTRICTION') && DEBUG_DISABLE_CHAT_5MIN_RESTRICTION) {
        return false;
    }

    // If appointment ID was passed, fetch the row
    if (is_numeric($appointment)) {
        $stmt = $conn->prepare("
            SELECT appointment_id, doctor_id, patient_id, appointment_date, 
                   appointment_time, appointment_duration, status, appointment_mode, 
                   appointment_type, follow_up_count, completed_at
            FROM appointments
            WHERE appointment_id = ?
        ");
        $stmt->bind_param("i", $appointment);
        $stmt->execute();
        $res = $stmt->get_result();
        $appointment = $res->fetch_assoc();
        $stmt->close();
        if (!$appointment) {
            return false;
        }
    }

    if (!is_array($appointment) || ($appointment['status'] ?? '') !== 'confirmed') {
        return false;
    }

    $times = getAppointmentSlotDateTimes($appointment);
    if (!$times['is_ended']) {
        return false;
    }

    $apptId = (int)$appointment['appointment_id'];
    $completedAt = $times['end_str'];

    // Atomic update: only update if status is still confirmed
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = 'completed', completed_at = ?
        WHERE appointment_id = ? AND status = 'confirmed'
    ");
    $stmt->bind_param("si", $completedAt, $apptId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        // Also update doctor_slots table if applicable
        $slotStmt = $conn->prepare("
            UPDATE doctor_slots 
            SET status = 'booked' 
            WHERE appointment_id = ?
        ");
        $slotStmt->bind_param("i", $apptId);
        $slotStmt->execute();
        $slotStmt->close();

        // Insert patient notification
        createNotification(
            $conn,
            (int)$appointment['patient_id'],
            'appointment_completed',
            $apptId,
            'Your consultation has concluded and has been marked as completed. You may book a follow-up within 3 days if eligible.'
        );

        return true;
    }

    return false;
}

/**
 * Batch checks and completes all past confirmed appointments
 * Called when patient or doctor appointment lists load (No cron!)
 *
 * @param mysqli $conn
 * @param int|null $userId Optional user ID filter (for doctor or patient)
 * @return int Number of completed appointments
 */
function lazyCompleteBatch(mysqli $conn, ?int $userId = null): int {
    // If debug mode is active, do not auto-expire confirmed appointments so chat can be tested anytime
    if (defined('DEBUG_DISABLE_CHAT_5MIN_RESTRICTION') && DEBUG_DISABLE_CHAT_5MIN_RESTRICTION) {
        return 0;
    }

    $now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $todayStr = $now->format('Y-m-d');
    $timeStr = $now->format('H:i:s');

    // Find confirmed appointments where slot end has passed:
    // Either date < today, or (date = today AND slot_start + duration <= current_time)
    $sql = "
        SELECT appointment_id, doctor_id, patient_id, appointment_date, 
               appointment_time, appointment_duration, status
        FROM appointments
        WHERE status = 'confirmed'
          AND (
              appointment_date < ?
              OR (
                  appointment_date = ? 
                  AND ADDTIME(appointment_time, SEC_TO_TIME(appointment_duration * 60)) <= ?
              )
          )
    ";

    $params = [$todayStr, $todayStr, $timeStr];
    $types = "sss";

    if ($userId !== null && $userId > 0) {
        $sql .= " AND (doctor_id = ? OR patient_id = ?)";
        $params[] = $userId;
        $params[] = $userId;
        $types .= "ii";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $completedCount = 0;
    while ($row = $result->fetch_assoc()) {
        if (lazyComplete($row, $conn)) {
            $completedCount++;
        }
    }
    $stmt->close();

    return $completedCount;
}
