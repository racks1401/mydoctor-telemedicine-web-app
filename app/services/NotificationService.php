<?php
/**
 * Shared Notification Service for Doctor and Patient panels
 */

class NotificationService {

    /**
     * Get user's notifications and unread count
     */
    public static function getUserNotifications(mysqli $conn, int $userId, int $limit = 50): array {
        // Fast unread count
        $cntStmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE user_id = ? AND is_read = 0");
        $cntStmt->bind_param("i", $userId);
        $cntStmt->execute();
        $unreadCount = (int)$cntStmt->get_result()->fetch_assoc()['unread'];
        $cntStmt->close();

        // Fetch notifications
        $stmt = $conn->prepare("
            SELECT notification_id, user_id, type, appointment_id, message, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC, notification_id DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Format created_at nicely (Asia/Kolkata)
        $tz = new DateTimeZone('Asia/Kolkata');
        $now = new DateTime('now', $tz);

        foreach ($notifications as &$n) {
            $n['notification_id'] = (int)$n['notification_id'];
            $n['user_id'] = (int)$n['user_id'];
            $n['is_read'] = (int)$n['is_read'];
            $n['appointment_id'] = !empty($n['appointment_id']) ? (int)$n['appointment_id'] : null;

            $notifTime = new DateTime($n['created_at'], $tz);
            $diff = $now->diff($notifTime);

            if ($diff->days == 0) {
                if ($diff->h == 0) {
                    $n['time_ago'] = ($diff->i <= 1) ? "Just now" : "{$diff->i}m ago";
                } else {
                    $n['time_ago'] = "{$diff->h}h ago";
                }
            } elseif ($diff->days == 1) {
                $n['time_ago'] = "Yesterday";
            } elseif ($diff->days < 7) {
                $n['time_ago'] = "{$diff->days}d ago";
            } else {
                $n['time_ago'] = $notifTime->format('M j, Y');
            }
            $n['formatted_datetime'] = $notifTime->format('M j, Y \a\t g:i A');
        }
        unset($n);

        return [
            'unread_count'  => $unreadCount,
            'notifications' => $notifications
        ];
    }

    /**
     * Get only unread count (lightweight polling / check)
     */
    public static function getUnreadCount(mysqli $conn, int $userId): int {
        $stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $count = (int)$stmt->get_result()->fetch_assoc()['unread'];
        $stmt->close();
        return $count;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead(mysqli $conn, int $userId): bool {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /**
     * Mark single notification as read
     */
    public static function markAsRead(mysqli $conn, int $notificationId, int $userId): bool {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notificationId, $userId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
