<?php
/**
 * ChatService
 * Service layer coordinating chat interactions between controllers/APIs and ChatModel.
 */

require_once __DIR__ . '/../models/ChatModel.php';

class ChatService {

    public static function getConversationList(mysqli $conn, int $userId): array {
        return ChatModel::getConversationList($conn, $userId);
    }

    public static function getOrCreateConversation(mysqli $conn, int $appointmentId, int $userId): array {
        return ChatModel::getOrCreateConversation($conn, $appointmentId, $userId);
    }

    public static function getConversationById(mysqli $conn, int $conversationId, int $userId): array {
        return ChatModel::getConversationById($conn, $conversationId, $userId);
    }

    public static function fetchMessages(
        mysqli $conn,
        int $conversationId,
        int $userId,
        ?int $afterId = null,
        ?int $beforeId = null,
        int $limit = 30
    ): array {
        return ChatModel::fetchMessages($conn, $conversationId, $userId, $afterId, $beforeId, $limit);
    }

    public static function sendMessage(mysqli $conn, int $conversationId, int $senderId, string $body, bool $deferSignal = false): array {
        return ChatModel::sendMessage($conn, $conversationId, $senderId, $body, $deferSignal);
    }

    public static function markMessagesRead(mysqli $conn, int $conversationId, int $userId): int {
        return ChatModel::markMessagesRead($conn, $conversationId, $userId);
    }
}
