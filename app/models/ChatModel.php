<?php
/**
 * ChatModel
 * Manages consultation conversations and messages for online appointments.
 * Strictly enforces server-side Asia/Kolkata timing, status rules, and membership.
 */

require_once __DIR__ . '/../helpers/appointment.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../../config/firebase.php';

class ChatModel {

    public static function formatProfileUrl(?string $url): string {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url) || str_starts_with($url, '//')) {
            return $url;
        }
        $clean = ltrim(preg_replace('#^(\.\./)+#', '', $url), '/');
        if (str_starts_with($clean, 'public/')) {
            return '/my_doctor/' . $clean;
        }
        if (str_starts_with($clean, 'my_doctor/')) {
            return '/' . $clean;
        }
        return '/my_doctor/public/' . $clean;
    }

    public static function formatDoctorName(?string $name): string {
        $name = trim((string)$name);
        if ($name === '') return 'Doctor';
        if (preg_match('/^Dr\.?\s+/i', $name)) {
            return $name;
        }
        return 'Dr. ' . $name;
    }

    public static function getInitials(?string $name): string {
        $name = trim((string)$name);
        if ($name === '') return 'MD';
        // Clean common prefixes if desired but keep simple
        $parts = preg_split('/\s+/', $name);
        $initials = '';
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $initials .= strtoupper(mb_substr($p, 0, 1));
                if (strlen($initials) >= 2) break;
            }
        }
        return $initials ?: 'MD';
    }

    /**
     * Retrieves all conversations for a user (doctor or patient)
     *
     * @param mysqli $conn
     * @param int $userId
     * @return array
     */
    public static function getConversationList(mysqli $conn, int $userId): array {
        // Query conversations where user is doctor or patient
        $sql = "
            SELECT 
                c.conversation_id,
                c.appointment_id,
                c.doctor_id,
                c.patient_id,
                c.last_message_at,
                c.created_at AS conv_created_at,
                a.appointment_date,
                a.appointment_time,
                a.appointment_duration,
                a.appointment_mode,
                a.appointment_type,
                a.status AS appointment_status,
                a.follow_up_count,
                a.completed_at,
                doc.name AS doctor_name,
                doc.profile_url AS doctor_profile_url,
                dinfo.specialization AS doctor_specialization,
                pat.name AS patient_name,
                pat.profile_url AS patient_profile_url
            FROM conversations c
            INNER JOIN appointments a ON c.appointment_id = a.appointment_id
            INNER JOIN users doc ON c.doctor_id = doc.user_id
            LEFT JOIN doctors dinfo ON doc.user_id = dinfo.user_id
            INNER JOIN users pat ON c.patient_id = pat.user_id
            WHERE (c.doctor_id = ? OR c.patient_id = ?)
              AND a.appointment_mode IN ('video_call', 'chat', 'audio_call')
            ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rawRows = [];
        $seenOtherUsers = [];

        while ($row = $result->fetch_assoc()) {
            $isDoctor = ($userId === (int)$row['doctor_id']);
            $otherUserId = $isDoctor ? (int)$row['patient_id'] : (int)$row['doctor_id'];

            // Strict unique constraint: One chat per user (like WhatsApp)
            if (isset($seenOtherUsers[$otherUserId])) {
                continue;
            }
            $seenOtherUsers[$otherUserId] = true;
            $rawRows[] = $row;
        }
        $stmt->close();

        if (empty($rawRows)) {
            return [];
        }

        // Batch fetch unread counts and last messages in bulk (avoids 2*N queries in loop)
        $convIds = array_map(fn($r) => (int)$r['conversation_id'], $rawRows);
        $inClause = implode(',', $convIds);

        // 1. Unread count per conversation
        $unreadMap = [];
        $unreadSql = "
            SELECT conversation_id, COUNT(*) AS unread 
            FROM messages 
            WHERE conversation_id IN ($inClause) 
              AND sender_id != ? 
              AND read_at IS NULL
            GROUP BY conversation_id
        ";
        $uStmt = $conn->prepare($unreadSql);
        $uStmt->bind_param("i", $userId);
        $uStmt->execute();
        $uRes = $uStmt->get_result();
        while ($uRow = $uRes->fetch_assoc()) {
            $unreadMap[(int)$uRow['conversation_id']] = (int)$uRow['unread'];
        }
        $uStmt->close();

        // 2. Last message per conversation
        $lastMsgMap = [];
        $lastMsgSql = "
            SELECT m.conversation_id, m.body, m.created_at
            FROM messages m
            INNER JOIN (
                SELECT conversation_id, MAX(message_id) AS max_id
                FROM messages
                WHERE conversation_id IN ($inClause)
                GROUP BY conversation_id
            ) sub ON m.message_id = sub.max_id
        ";
        $mRes = $conn->query($lastMsgSql);
        if ($mRes) {
            while ($mRow = $mRes->fetch_assoc()) {
                $lastMsgMap[(int)$mRow['conversation_id']] = [
                    'body'       => $mRow['body'],
                    'created_at' => $mRow['created_at']
                ];
            }
            $mRes->free();
        }

        $conversations = [];
        foreach ($rawRows as $row) {
            $convId = (int)$row['conversation_id'];
            $slotTimes = getAppointmentSlotDateTimes($row);
            $isDoctor = ($userId === (int)$row['doctor_id']);

            // Calculate window state without DB update (lazyComplete is executed only on opened chat)
            $windowState = 'not_started';
            if ($slotTimes['chat_window_open']) {
                $windowState = 'live';
            } elseif ($slotTimes['chat_window_ended'] || $row['appointment_status'] === 'completed') {
                $windowState = 'ended';
            }

            // Can send messages
            $canSend = ($row['appointment_status'] === 'confirmed' && $slotTimes['chat_window_open']);

            // Follow-up eligibility (consultation completed within 3 days)
            $canFollowUp = false;
            if ($row['appointment_status'] === 'completed' &&
                $row['appointment_type'] === 'consultation' &&
                (int)$row['follow_up_count'] === 0) {
                $threeDaysAfterEnd = (clone $slotTimes['end'])->modify('+3 days');
                if ($slotTimes['now'] <= $threeDaysAfterEnd) {
                    $canFollowUp = true;
                }
            }

            // Other user details
            $otherUserId = $isDoctor ? (int)$row['patient_id'] : (int)$row['doctor_id'];
            $otherUserName = $isDoctor ? $row['patient_name'] : self::formatDoctorName($row['doctor_name']);
            $otherUserAvatar = self::formatProfileUrl($isDoctor ? $row['patient_profile_url'] : $row['doctor_profile_url']);
            $otherUserInitials = self::getInitials($otherUserName);
            $otherUserRole = $isDoctor ? 'Patient' : ($row['doctor_specialization'] ?: 'Doctor');

            $lastMsg = $lastMsgMap[$convId]['body'] ?? '';
            $lastMsgAt = $lastMsgMap[$convId]['created_at'] ?? $row['last_message_at'];
            $unreadCount = $unreadMap[$convId] ?? 0;

            $conversations[] = [
                'conversation_id'         => $convId,
                'appointment_id'          => (int)$row['appointment_id'],
                'doctor_id'               => (int)$row['doctor_id'],
                'patient_id'              => (int)$row['patient_id'],
                'other_user_id'           => $otherUserId,
                'other_user_name'         => $otherUserName,
                'other_user_avatar'       => $otherUserAvatar,
                'other_user_initials'     => $otherUserInitials,
                'other_user_role'         => $otherUserRole,
                'appointment_mode'        => $row['appointment_mode'],
                'appointment_type'        => $row['appointment_type'],
                'appointment_status'      => $row['appointment_status'],
                'appointment_date'        => $row['appointment_date'],
                'appointment_time'        => $row['appointment_time'],
                'appointment_duration'    => (int)$row['appointment_duration'],
                'slot_start'              => $slotTimes['start_str'],
                'slot_end'                => $slotTimes['end_str'],
                'chat_start'              => $slotTimes['chat_start_str'],
                'window_state'            => $windowState,
                'can_send'                => $canSend,
                'can_follow_up'           => $canFollowUp,
                'last_message'            => $lastMsg,
                'last_message_at'         => $lastMsgAt,
                'unread_count'            => $unreadCount
            ];
        }

        return $conversations;
    }

    /**
     * Gets or creates a conversation for a specific online appointment
     *
     * @param mysqli $conn
     * @param int $appointmentId
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public static function getOrCreateConversation(mysqli $conn, int $appointmentId, int $userId): array {
        // Fetch appointment
        $stmt = $conn->prepare("
            SELECT appointment_id, doctor_id, patient_id, appointment_date,
                   appointment_time, appointment_duration, appointment_mode,
                   appointment_type, status, follow_up_count, completed_at
            FROM appointments
            WHERE appointment_id = ?
        ");
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $appt = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$appt) {
            throw new Exception("Appointment not found");
        }

        // Validate membership
        if ($userId !== (int)$appt['doctor_id'] && $userId !== (int)$appt['patient_id']) {
            throw new Exception("Unauthorized access to this consultation");
        }

        // Validate online mode
        if (!in_array($appt['appointment_mode'], ['video_call', 'chat', 'audio_call'], true)) {
            throw new Exception("Chat is only available for online consultations");
        }

        // Lazy-complete check
        if ($appt['status'] === 'confirmed') {
            if (lazyComplete($appt, $conn)) {
                $appt['status'] = 'completed';
            }
        }

        // Check if conversation exists for this appointment OR between this doctor & patient
        $docId = (int)$appt['doctor_id'];
        $patId = (int)$appt['patient_id'];

        $stmt = $conn->prepare("
            SELECT conversation_id 
            FROM conversations 
            WHERE appointment_id = ? 
               OR (doctor_id = ? AND patient_id = ?)
            ORDER BY (appointment_id = ?) DESC, conversation_id DESC 
            LIMIT 1
        ");
        $stmt->bind_param("iiii", $appointmentId, $docId, $patId, $appointmentId);
        $stmt->execute();
        $convRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $conversationId = 0;
        if ($convRow) {
            $conversationId = (int)$convRow['conversation_id'];
            // Link existing conversation to active appointment
            $upd = $conn->prepare("UPDATE conversations SET appointment_id = ? WHERE conversation_id = ?");
            $upd->bind_param("ii", $appointmentId, $conversationId);
            $upd->execute();
            $upd->close();
        } else {
            // Create conversation
            $insert = $conn->prepare("
                INSERT INTO conversations (appointment_id, doctor_id, patient_id, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $insert->bind_param("iii", $appointmentId, $docId, $patId);
            $insert->execute();
            $conversationId = (int)$conn->insert_id;
            $insert->close();

            // Set members in Firebase once at conversation creation
            if (function_exists('setConversationMembers')) {
                try {
                    setConversationMembers($conversationId, $docId, $patId);
                } catch (\Throwable $e) {}
            }
        }

        return self::getConversationById($conn, $conversationId, $userId);
    }

    /**
     * Gets conversation details by conversation ID
     *
     * @param mysqli $conn
     * @param int $conversationId
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public static function getConversationById(mysqli $conn, int $conversationId, int $userId): array {
        $sql = "
            SELECT 
                c.conversation_id,
                c.appointment_id,
                c.doctor_id,
                c.patient_id,
                c.last_message_at,
                c.created_at AS conv_created_at,
                a.appointment_date,
                a.appointment_time,
                a.appointment_duration,
                a.appointment_mode,
                a.appointment_type,
                a.status AS appointment_status,
                a.follow_up_count,
                a.completed_at,
                doc.name AS doctor_name,
                doc.profile_url AS doctor_profile_url,
                dinfo.specialization AS doctor_specialization,
                pat.name AS patient_name,
                pat.profile_url AS patient_profile_url
            FROM conversations c
            INNER JOIN appointments a ON c.appointment_id = a.appointment_id
            INNER JOIN users doc ON c.doctor_id = doc.user_id
            LEFT JOIN doctors dinfo ON doc.user_id = dinfo.user_id
            INNER JOIN users pat ON c.patient_id = pat.user_id
            WHERE c.conversation_id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            throw new Exception("Conversation not found");
        }

        // Validate membership
        if ($userId !== (int)$row['doctor_id'] && $userId !== (int)$row['patient_id']) {
            throw new Exception("Forbidden: You are not a participant in this conversation");
        }

        // Lazy-complete check (only for this opened appointment)
        if ($row['appointment_status'] === 'confirmed') {
            if (lazyComplete($row, $conn)) {
                $row['appointment_status'] = 'completed';
            }
        }

        $slotTimes = getAppointmentSlotDateTimes($row);
        $isDoctor = ($userId === (int)$row['doctor_id']);

        $windowState = 'not_started';
        if ($slotTimes['chat_window_open']) {
            $windowState = 'live';
        } elseif ($slotTimes['chat_window_ended'] || $row['appointment_status'] === 'completed') {
            $windowState = 'ended';
        }

        $canSend = ($row['appointment_status'] === 'confirmed' && $slotTimes['chat_window_open']);

        $canFollowUp = false;
        if ($row['appointment_status'] === 'completed' &&
            $row['appointment_type'] === 'consultation' &&
            (int)$row['follow_up_count'] === 0) {
            $threeDaysAfterEnd = (clone $slotTimes['end'])->modify('+3 days');
            if ($slotTimes['now'] <= $threeDaysAfterEnd) {
                $canFollowUp = true;
            }
        }

        $otherUserId = $isDoctor ? (int)$row['patient_id'] : (int)$row['doctor_id'];
        $otherUserName = $isDoctor ? $row['patient_name'] : self::formatDoctorName($row['doctor_name']);
        $otherUserAvatar = self::formatProfileUrl($isDoctor ? $row['patient_profile_url'] : $row['doctor_profile_url']);
        $otherUserInitials = self::getInitials($otherUserName);
        $otherUserRole = $isDoctor ? 'Patient' : ($row['doctor_specialization'] ?: 'Doctor');

        return [
            'conversation_id'         => (int)$row['conversation_id'],
            'appointment_id'          => (int)$row['appointment_id'],
            'doctor_id'               => (int)$row['doctor_id'],
            'patient_id'              => (int)$row['patient_id'],
            'other_user_id'           => $otherUserId,
            'other_user_name'         => $otherUserName,
            'other_user_avatar'       => $otherUserAvatar,
            'other_user_initials'     => $otherUserInitials,
            'other_user_role'         => $otherUserRole,
            'appointment_mode'        => $row['appointment_mode'],
            'appointment_type'        => $row['appointment_type'],
            'appointment_status'      => $row['appointment_status'],
            'appointment_date'        => $row['appointment_date'],
            'appointment_time'        => $row['appointment_time'],
            'appointment_duration'    => (int)$row['appointment_duration'],
            'slot_start'              => $slotTimes['start_str'],
            'slot_end'                => $slotTimes['end_str'],
            'chat_start'              => $slotTimes['chat_start_str'],
            'window_state'            => $windowState,
            'can_send'                => $canSend,
            'can_follow_up'           => $canFollowUp
        ];
    }

    /**
     * Fetches messages for a conversation
     * Supports after_id (new messages), before_id (pagination for older), or last 30
     *
     * @param mysqli $conn
     * @param int $conversationId
     * @param int $userId
     * @param int|null $afterId
     * @param int|null $beforeId
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public static function fetchMessages(
        mysqli $conn,
        int $conversationId,
        int $userId,
        ?int $afterId = null,
        ?int $beforeId = null,
        int $limit = 30
    ): array {
        $conv = self::getConversationById($conn, $conversationId, $userId);

        if ($limit <= 0 || $limit > 100) {
            $limit = 30;
        }

        $messages = [];

        if ($afterId !== null && $afterId > 0) {
            // New messages since afterId
            $stmt = $conn->prepare("
                SELECT message_id, conversation_id, sender_id, body, created_at, read_at
                FROM messages USE INDEX (idx_conv_msg)
                WHERE conversation_id = ? AND message_id > ?
                ORDER BY message_id ASC
                LIMIT ?
            ");
            $stmt->bind_param("iii", $conversationId, $afterId, $limit);
        } elseif ($beforeId !== null && $beforeId > 0) {
            // Older messages before beforeId (reverse to chronological order)
            $stmt = $conn->prepare("
                SELECT message_id, conversation_id, sender_id, body, created_at, read_at
                FROM (
                    SELECT message_id, conversation_id, sender_id, body, created_at, read_at
                    FROM messages USE INDEX (idx_conv_msg)
                    WHERE conversation_id = ? AND message_id < ?
                    ORDER BY message_id DESC
                    LIMIT ?
                ) sub
                ORDER BY message_id ASC
            ");
            $stmt->bind_param("iii", $conversationId, $beforeId, $limit);
        } else {
            // Initial load: last 30 messages in ascending order
            $stmt = $conn->prepare("
                SELECT message_id, conversation_id, sender_id, body, created_at, read_at
                FROM (
                    SELECT message_id, conversation_id, sender_id, body, created_at, read_at
                    FROM messages USE INDEX (idx_conv_msg)
                    WHERE conversation_id = ?
                    ORDER BY message_id DESC
                    LIMIT ?
                ) sub
                ORDER BY message_id ASC
            ");
            $stmt->bind_param("ii", $conversationId, $limit);
        }

        $stmt->execute();
        $res = $stmt->get_result();

        while ($m = $res->fetch_assoc()) {
            $isMe = ((int)$m['sender_id'] === $userId);
            $dt = new DateTime($m['created_at']);
            $timeFormatted = $dt->format('g:i A');

            $messages[] = [
                'message_id'      => (int)$m['message_id'],
                'conversation_id' => (int)$m['conversation_id'],
                'sender_id'       => (int)$m['sender_id'],
                'body'            => $m['body'],
                'created_at'      => $m['created_at'],
                'time_formatted'  => $timeFormatted,
                'read_at'         => $m['read_at'],
                'is_me'           => $isMe
            ];
        }
        $stmt->close();

        return [
            'conversation'   => $conv,
            'messages'       => $messages,
            'window_state'   => $conv['window_state'],
            'can_send'       => $conv['can_send'],
            'can_follow_up'  => $conv['can_follow_up']
        ];
    }

    /**
     * Sends a message in a consultation conversation
     * Validates:
     * - Status is 'confirmed'
     * - Now is between slot start - 5 minutes and slot end
     * - Body trimmed, non-empty, max 2000 chars
     *
     * @param mysqli $conn
     * @param int $conversationId
     * @param int $senderId
     * @param string $body
     * @return array
     * @throws Exception
     */
    public static function sendMessage(mysqli $conn, int $conversationId, int $senderId, string $body, bool $deferSignal = false): array {
        // Validate message body
        $body = trim($body);
        if ($body === '') {
            return [
                'success' => false,
                'code'    => 400,
                'error'   => 'Message body cannot be empty.'
            ];
        }

        if (mb_strlen($body) > 2000) {
            return [
                'success' => false,
                'code'    => 400,
                'error'   => 'Message cannot exceed 2000 characters.'
            ];
        }

        // Fetch conversation & appointment
        $conv = self::getConversationById($conn, $conversationId, $senderId);

        // Verify appointment mode is online
        if (!in_array($conv['appointment_mode'], ['video_call', 'chat', 'audio_call'], true)) {
            return [
                'success' => false,
                'code'    => 400,
                'error'   => 'Chat is only available for online consultations.'
            ];
        }

        // Verify chat window & status
        if ($conv['appointment_status'] !== 'confirmed') {
            return [
                'success' => false,
                'code'    => 403,
                'error'   => "Chat window is closed. Appointment status is currently '{$conv['appointment_status']}'."
            ];
        }

        if (!$conv['can_send']) {
            return [
                'success' => false,
                'code'    => 403,
                'error'   => 'Chat window is closed. Messaging is only permitted between 5 minutes before the scheduled slot and when the slot ends.'
            ];
        }

        // Insert message
        $stmt = $conn->prepare("
            INSERT INTO messages (conversation_id, sender_id, body, created_at, read_at)
            VALUES (?, ?, ?, NOW(), NULL)
        ");
        $stmt->bind_param("iis", $conversationId, $senderId, $body);
        $stmt->execute();
        $messageId = (int)$conn->insert_id;
        $stmt->close();

        // Update conversation last_message_at
        $updateConv = $conn->prepare("
            UPDATE conversations 
            SET last_message_at = NOW() 
            WHERE conversation_id = ?
        ");
        $updateConv->bind_param("i", $conversationId);
        $updateConv->execute();
        $updateConv->close();

        // Push lightweight Firebase real-time signal if not deferred
        $receiverId = ($senderId === (int)$conv['doctor_id']) ? (int)$conv['patient_id'] : (int)$conv['doctor_id'];
        if (!$deferSignal && function_exists('pushChatMessageSignal')) {
            try {
                pushChatMessageSignal($conversationId, $messageId, $senderId, $receiverId, time());
            } catch (\Throwable $e) {}
        }

        $nowDt = new DateTime('now', new DateTimeZone('Asia/Kolkata'));

        return [
            'success'     => true,
            'receiver_id' => $receiverId,
            'message'     => [
                'message_id'      => $messageId,
                'conversation_id' => $conversationId,
                'sender_id'       => $senderId,
                'body'            => $body,
                'created_at'      => $nowDt->format('Y-m-d H:i:s'),
                'time_formatted'  => $nowDt->format('g:i A'),
                'read_at'         => null,
                'is_me'           => true
            ]
        ];
    }

    /**
     * Marks unread messages in a conversation as read for the current user
     *
     * @param mysqli $conn
     * @param int $conversationId
     * @param int $userId
     * @return int
     */
    public static function markMessagesRead(mysqli $conn, int $conversationId, int $userId): int {
        // Membership check
        $stmt = $conn->prepare("
            SELECT doctor_id, patient_id 
            FROM conversations 
            WHERE conversation_id = ?
        ");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $conv = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$conv) {
            return 0;
        }

        if ($userId !== (int)$conv['doctor_id'] && $userId !== (int)$conv['patient_id']) {
            return 0;
        }

        $update = $conn->prepare("
            UPDATE messages 
            SET read_at = NOW() 
            WHERE conversation_id = ? 
              AND sender_id != ? 
              AND read_at IS NULL
        ");
        $update->bind_param("ii", $conversationId, $userId);
        $update->execute();
        $affected = $update->affected_rows;
        $update->close();

        return $affected;
    }

    /**
     * Helper to get last message preview and unread count for conversation
     *
     * @param mysqli $conn
     * @param int $conversationId
     * @param int $userId
     * @return array
     */
    private static function getLastMessageInfo(mysqli $conn, int $conversationId, int $userId): array {
        // Last message
        $stmt = $conn->prepare("
            SELECT body, created_at 
            FROM messages 
            WHERE conversation_id = ? 
            ORDER BY message_id DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $lastRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Unread count
        $unreadStmt = $conn->prepare("
            SELECT COUNT(*) AS unread 
            FROM messages 
            WHERE conversation_id = ? 
              AND sender_id != ? 
              AND read_at IS NULL
        ");
        $unreadStmt->bind_param("ii", $conversationId, $userId);
        $unreadStmt->execute();
        $unreadRow = $unreadStmt->get_result()->fetch_assoc();
        $unreadStmt->close();

        return [
            'last_message'    => $lastRow ? $lastRow['body'] : '',
            'last_message_at' => $lastRow ? $lastRow['created_at'] : null,
            'unread_count'    => (int)($unreadRow['unread'] ?? 0)
        ];
    }
}
