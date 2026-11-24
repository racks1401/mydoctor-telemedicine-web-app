<?php
/**
 * WebRTC Call End API Endpoint
 * Handles membership validation, deletes calls/{convId}/signal in Firebase RTDB,
 * and cleans up active call state.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/firebase.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        jsonResponse(false, "Method not allowed. Use POST.");
        exit;
    }

    $userId = me();
    if (!$userId) {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access. Please login first.");
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $jsonData = [];
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $jsonData = $decoded;
        }
    }

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

    session_write_close();

    $conversationId = (int)($_POST['conversation_id'] ?? $jsonData['conversation_id'] ?? 0);
    if ($conversationId <= 0) {
        http_response_code(400);
        jsonResponse(false, "Missing or invalid conversation ID.");
        exit;
    }

    // Verify conversation membership
    $stmt = $conn->prepare("
        SELECT doctor_id, patient_id
        FROM conversations
        WHERE conversation_id = ?
    ");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        jsonResponse(false, "Conversation not found.");
        exit;
    }

    $docId = (int)$row['doctor_id'];
    $patId = (int)$row['patient_id'];

    if ($userId !== $docId && $userId !== $patId) {
        http_response_code(403);
        jsonResponse(false, "Forbidden: You are not a participant in this conversation.");
        exit;
    }

    // Delete calls/{convId}/signal in Firebase RTDB
    if (function_exists('clearCallSignal')) {
        clearCallSignal($conversationId);
    }

    // Also remove caller/callee presence if possible
    try {
        $db = getFirebaseDatabase();
        if ($db) {
            $db->getReference("calls/{$conversationId}/presence/{$userId}")->remove();
        }
    } catch (\Throwable $e) {}

    // Complete ongoing call record in call_logs
    try {
        $stmtEnd = $conn->prepare("
            UPDATE call_logs 
            SET status = 'completed',
                ended_at = NOW(),
                duration_seconds = GREATEST(1, TIMESTAMPDIFF(SECOND, started_at, NOW()))
            WHERE conversation_id = ? AND status = 'ongoing'
            ORDER BY id DESC LIMIT 1
        ");
        if ($stmtEnd) {
            $stmtEnd->bind_param("i", $conversationId);
            $stmtEnd->execute();
            $stmtEnd->close();
        }
    } catch (\Throwable $e) {}

    jsonResponse(true, "Call ended successfully and signals cleared", [
        'conversation_id' => $conversationId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, "Server error: " . $e->getMessage());
}
