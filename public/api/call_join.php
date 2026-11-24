<?php
/**
 * WebRTC Call Join API Endpoint
 * Handles membership validation, lazyComplete, online/confirmed/time window checks,
 * registers allowedUntil in Firebase RTDB, and returns role + ICE server configuration.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/appointment.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/firebase.php';
require_once __DIR__ . '/../../config/webrtc.php';

try {
    // 1. Method verification
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        jsonResponse(false, "Method not allowed. Use POST.");
        exit;
    }

    // 2. Authentication check
    $userId = me();
    if (!$userId) {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access. Please login first.");
        exit;
    }

    // 3. Parse input payload
    $rawInput = file_get_contents('php://input');
    $jsonData = [];
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $jsonData = $decoded;
        }
    }

    // 4. CSRF validation
    if (!empty($_SESSION['user_id'])) {
        $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? $_POST['csrf_token'] 
            ?? $jsonData['csrf_token'] 
            ?? '';

        if (!verifyCsrfToken($clientToken)) {
            http_response_code(403);
            jsonResponse(false, "Invalid or missing CSRF security token.");
            exit;
        }
    }

    // Close session immediately so concurrent requests are not blocked
    session_write_close();

    $conversationId = (int)($_POST['conversation_id'] ?? $jsonData['conversation_id'] ?? 0);
    if ($conversationId <= 0) {
        http_response_code(400);
        jsonResponse(false, "Missing or invalid conversation ID.");
        exit;
    }

    // 5. Query conversation and appointment
    $stmt = $conn->prepare("
        SELECT 
            c.conversation_id,
            c.appointment_id,
            c.doctor_id,
            c.patient_id,
            a.appointment_date,
            a.appointment_time,
            a.appointment_duration,
            a.appointment_mode,
            a.appointment_type,
            a.status AS appointment_status,
            a.follow_up_count,
            a.completed_at
        FROM conversations c
        INNER JOIN appointments a ON c.appointment_id = a.appointment_id
        WHERE c.conversation_id = ?
    ");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(403);
        jsonResponse(false, "Conversation not found or access denied.");
        exit;
    }

    $docId = (int)$row['doctor_id'];
    $patId = (int)$row['patient_id'];

    // 6. Membership verification
    if ($userId !== $docId && $userId !== $patId) {
        http_response_code(403);
        jsonResponse(false, "Forbidden: You are not a participant in this consultation.");
        exit;
    }

    // 7. Lazy-complete check for opened appointment
    if ($row['appointment_status'] === 'confirmed') {
        if (lazyComplete($row, $conn)) {
            $row['appointment_status'] = 'completed';
        }
    }

    // 8. Online appointment mode check
    if (!in_array($row['appointment_mode'], ['video_call', 'chat', 'audio_call'], true)) {
        http_response_code(403);
        jsonResponse(false, "Call is only available for online consultations.");
        exit;
    }

    // 9. Appointment status check
    if ($row['appointment_status'] !== 'confirmed') {
        http_response_code(403);
        jsonResponse(false, "Call window is closed. Appointment status is currently '{$row['appointment_status']}'.");
        exit;
    }

    // 10. Time window check (Asia/Kolkata, slot start - 5m until slot end)
    $slotTimes = getAppointmentSlotDateTimes($row);
    if (!$slotTimes['chat_window_open']) {
        http_response_code(403);
        jsonResponse(false, "Call window is closed. Calls are only permitted from 5 minutes before scheduled slot until slot ends.");
        exit;
    }

    // Calculate allowedUntil timestamp in milliseconds
    $slotEndMs = (int)($slotTimes['end']->getTimestamp() * 1000);
    if (defined('DEBUG_DISABLE_CHAT_5MIN_RESTRICTION') && DEBUG_DISABLE_CHAT_5MIN_RESTRICTION) {
        $allowedUntilMs = max($slotEndMs, (time() + 86400) * 1000);
    } else {
        $allowedUntilMs = $slotEndMs;
    }

    // 11. Write calls/{convId}/meta/allowedUntil and ensure members populated in Firebase
    if (function_exists('setCallAllowedUntil')) {
        setCallAllowedUntil($conversationId, $allowedUntilMs);
    }
    if (function_exists('setConversationMembers')) {
        setConversationMembers($conversationId, $docId, $patId);
    }

    // 12. Role assignment: Doctor is caller, Patient is callee/answerer
    $role = ($userId === $docId) ? 'caller' : 'callee';

    // When caller joins, clear any previous call signals to ensure fresh negotiation
    if ($role === 'caller' && function_exists('clearCallSignal')) {
        clearCallSignal($conversationId);
        
        // Log ongoing call into call_logs
        try {
            $callType = ($row['appointment_mode'] === 'audio_call') ? 'audio' : 'video';
            $appId = (int)$row['appointment_id'];
            $stmtLog = $conn->prepare("
                INSERT INTO call_logs (conversation_id, appointment_id, doctor_id, patient_id, caller_id, receiver_id, call_type, status, started_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'ongoing', NOW())
            ");
            if ($stmtLog) {
                $stmtLog->bind_param("iiiiiss", $conversationId, $appId, $docId, $patId, $userId, $patId, $callType);
                $stmtLog->execute();
                $stmtLog->close();
            }
        } catch (\Throwable $e) {}
    }

    // 13. ICE servers configuration
    $iceServers = getWebRtcIceServers();

    jsonResponse(true, "Call joined successfully", [
        'role'            => $role,
        'allowedUntil'    => $allowedUntilMs,
        'iceServers'      => $iceServers,
        'conversation_id' => $conversationId,
        'user_id'         => $userId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, "Server error: " . $e->getMessage());
}
