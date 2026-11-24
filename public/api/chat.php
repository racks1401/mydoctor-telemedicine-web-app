<?php
/**
 * Unified Chat API Endpoint
 * Handles conversation listing, message fetching, message sending, and read receipts.
 * Strictly enforces server-side timing, membership, CSRF, rate-limiting, and error codes.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../app/helpers/response.php';
require_once __DIR__ . '/../../app/helpers/auth.php';
require_once __DIR__ . '/../../app/helpers/appointment.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/services/ChatService.php';

$requestStartTime = microtime(true);

try {
    // 1. Authentication check
    $userId = me();
    if (!$userId) {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access. Please login first.");
        exit;
    }

    // 2. Parse request payload
    $rawInput = file_get_contents('php://input');
    $jsonData = [];
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $jsonData = $decoded;
        }
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? $jsonData['action'] ?? 'list';

    switch ($action) {

        case 'list':
        case 'conversations':
            $t0 = microtime(true);
            session_write_close();

            $conversations = ChatService::getConversationList($conn, $userId);
            $durationMs = round((microtime(true) - $t0) * 1000, 2);
            header("X-Timing-List-Ms: {$durationMs}");

            jsonResponse(true, "Conversations retrieved successfully", [
                'conversations' => $conversations,
                'count'         => count($conversations),
                '_timing_ms'    => $durationMs
            ]);
            break;

        case 'get_conversation':
        case 'create_conversation':
            $appointmentId = (int)($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? $jsonData['appointment_id'] ?? 0);
            $conversationId = (int)($_GET['conversation_id'] ?? $_POST['conversation_id'] ?? $jsonData['conversation_id'] ?? 0);

            if ($appointmentId <= 0 && $conversationId <= 0) {
                http_response_code(400);
                jsonResponse(false, "Invalid appointment ID or conversation ID.");
                exit;
            }

            // Read-only check: close session
            session_write_close();

            if ($conversationId > 0) {
                $conv = ChatService::getConversationById($conn, $conversationId, $userId);
            } else {
                $conv = ChatService::getOrCreateConversation($conn, $appointmentId, $userId);
            }

            jsonResponse(true, "Conversation details retrieved", $conv);
            break;

        case 'fetch_messages':
            $conversationId = (int)($_GET['conversation_id'] ?? $_POST['conversation_id'] ?? $jsonData['conversation_id'] ?? 0);
            if ($conversationId <= 0) {
                http_response_code(400);
                jsonResponse(false, "Missing conversation ID.");
                exit;
            }

            $afterId = isset($_GET['after_id']) ? (int)$_GET['after_id'] : (isset($jsonData['after_id']) ? (int)$jsonData['after_id'] : null);
            $beforeId = isset($_GET['before_id']) ? (int)$_GET['before_id'] : (isset($jsonData['before_id']) ? (int)$jsonData['before_id'] : null);
            $limit = (int)($_GET['limit'] ?? $jsonData['limit'] ?? 30);

            // Read-only endpoint: close session immediately to release lock
            session_write_close();

            $tFetch0 = microtime(true);
            $data = ChatService::fetchMessages($conn, $conversationId, $userId, $afterId, $beforeId, $limit);
            $fetchMs = round((microtime(true) - $tFetch0) * 1000, 2);
            header("X-Timing-Fetch-Ms: {$fetchMs}");
            $data['_timing_ms'] = $fetchMs;

            jsonResponse(true, "Messages fetched successfully", $data);
            break;

        case 'send_message':
            // Must be POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                jsonResponse(false, "Method not allowed. Use POST.");
                exit;
            }

            // CSRF validation: require valid CSRF token if session-based
            if (!empty($_SESSION['user_id'])) {
                $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] 
                    ?? $_POST['csrf_token'] 
                    ?? $jsonData['csrf_token'] 
                    ?? '';

                if (!verifyCsrfToken($clientToken)) {
                    http_response_code(403);
                    jsonResponse(false, "Invalid or missing CSRF token.");
                    exit;
                }
            }

            // Rate-limiting: 1 message per second per session
            $nowTime = microtime(true);
            if (!empty($_SESSION['last_chat_msg_ts']) && ($nowTime - $_SESSION['last_chat_msg_ts']) < 1.0) {
                http_response_code(429);
                jsonResponse(false, "You are sending messages too quickly. Please wait a moment.");
                exit;
            }
            $_SESSION['last_chat_msg_ts'] = $nowTime;

            // Release session lock immediately so concurrent user requests are not blocked
            session_write_close();

            $conversationId = (int)($_POST['conversation_id'] ?? $jsonData['conversation_id'] ?? 0);
            $body = (string)($_POST['body'] ?? $jsonData['body'] ?? '');

            if ($conversationId <= 0) {
                http_response_code(400);
                jsonResponse(false, "Missing conversation ID.");
                exit;
            }

            // 1. Fast MySQL insert (defer Firebase push to after response is flushed)
            $tSend0 = microtime(true);
            $result = ChatService::sendMessage($conn, $conversationId, $userId, $body, true);
            $sendMs = round((microtime(true) - $tSend0) * 1000, 2);

            if (!$result['success']) {
                $statusCode = $result['code'] ?? 400;
                http_response_code($statusCode);
                jsonResponse(false, $result['error']);
                exit;
            }

            // 2. Build JSON response and immediately flush to browser
            $out = json_encode([
                'status'   => true,
                'message'  => 'Message sent successfully',
                'data'     => $result['message'],
                '_send_ms' => $sendMs
            ]);

            ignore_user_abort(true);
            header("Content-Type: application/json; charset=UTF-8");
            header("X-Timing-Send-Ms: {$sendMs}");
            header("Connection: close");
            header("Content-Length: " . strlen($out));
            echo $out;
            flush();
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // 3. Background Firebase push (client already received response in <50ms)
            try {
                $receiverId = $result['receiver_id'] ?? null;
                pushChatMessageSignal($conversationId, (int)$result['message']['message_id'], $userId, $receiverId, time());
            } catch (\Throwable $e) {
                error_log("Background Firebase push error: " . $e->getMessage());
            }
            exit;

        case 'mark_read':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                jsonResponse(false, "Method not allowed. Use POST.");
                exit;
            }

            session_write_close();

            $conversationId = (int)($_POST['conversation_id'] ?? $jsonData['conversation_id'] ?? 0);
            if ($conversationId <= 0) {
                http_response_code(400);
                jsonResponse(false, "Missing conversation ID.");
                exit;
            }

            $affected = ChatService::markMessagesRead($conn, $conversationId, $userId);
            jsonResponse(true, "Messages marked as read", ['affected_rows' => $affected]);
            break;

        default:
            http_response_code(400);
            jsonResponse(false, "Unknown action '{$action}'.");
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, $e->getMessage());
}
