<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/patient/DashboardService.php";
require_once __DIR__ . "/../../../app/models/UserModel.php";

try {
    $userId = $_SESSION['user_id'] ?? null;
    $email = $_SESSION['email'] ?? null;

    if (!$userId && !$email) {
        jsonResponse(false, "Unauthorized access. Please log in.");
    }

    // Resolve user_id if only email in session
    if (!$userId && $email) {
        $s = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $s->bind_param("s", $email);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        $userId = $r['user_id'] ?? null;
    }

    if (!$userId) {
        jsonResponse(false, "User account not found.");
    }

    $userRole = strtolower($_SESSION['usertype'] ?? '');
    if ($userRole !== 'patient') {
        jsonResponse(false, "Unauthorized: Patient access only.");
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Helper: get list of columns in users table
    $columnsResult = $conn->query("SHOW COLUMNS FROM users");
    $existingColumns = [];
    if ($columnsResult) {
        while ($col = $columnsResult->fetch_assoc()) {
            $existingColumns[] = $col['Field'];
        }
    }

    switch ($action) {
        case "getSettings":
            // Fetch everything available in users for this user
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user) {
                jsonResponse(false, "User not found");
            }

            // Exclude password from response
            unset($user['password']);

            // Provide intelligent defaults for columns that might not exist yet
            $defaults = [
                'name'                  => $user['name'] ?? '',
                'email'                 => $user['email'] ?? '',
                'phone'                 => $user['phone'] ?? '',
                'dob'                   => $user['dob'] ?? '',
                'gender'                => $user['gender'] ?? 'Other',
                'blood_type'            => $user['blood_type'] ?? ($user['blood_group'] ?? 'O+'),
                'address'               => $user['address'] ?? '',
                'city'                  => $user['city'] ?? '',
                'state'                 => $user['state'] ?? '',
                'pin'                   => $user['pin'] ?? '',
                'appointment_reminders' => isset($user['appointment_reminders']) ? (int)$user['appointment_reminders'] : 1,
                'message_notifications' => isset($user['message_notifications']) ? (int)$user['message_notifications'] : 1,
                'health_alerts'         => isset($user['health_alerts']) ? (int)$user['health_alerts'] : 1,
                'newsletter'            => isset($user['newsletter']) ? (int)$user['newsletter'] : 0,
                'email_notifications'   => isset($user['email_notifications']) ? (int)$user['email_notifications'] : 1,
                'sms_notifications'     => isset($user['sms_notifications']) ? (int)$user['sms_notifications'] : 0,
                'two_factor_enabled'    => isset($user['two_factor_enabled']) ? (int)$user['two_factor_enabled'] : 0,
                'share_health_data'     => isset($user['share_health_data']) ? (int)$user['share_health_data'] : 1,
                'last_login_at'         => $user['last_login_at'] ?? date('Y-m-d H:i:s'),
                'last_login_ip'         => $user['last_login_ip'] ?? '127.0.0.1',
                'created_at'            => $user['created_at'] ?? ''
            ];

            // Merge so all fields are available dynamically
            $responsePayload = array_merge($defaults, $user);

            jsonResponse(true, "Settings loaded successfully", $responsePayload);
            break;

        case "updateProfile":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

            $allowedFields = ['name', 'phone', 'dob', 'gender', 'address', 'city', 'state', 'pin', 'blood_type', 'blood_group'];
            $updates = [];
            $types = "";
            $values = [];

            foreach ($allowedFields as $field) {
                if (isset($input[$field]) && in_array($field, $existingColumns, true)) {
                    $updates[] = "`{$field}` = ?";
                    $types .= "s";
                    $values[] = trim((string)$input[$field]);
                }
            }

            if (!empty($updates)) {
                $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
                $types .= "i";
                $values[] = $userId;

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
                if (!$stmt->execute()) {
                    jsonResponse(false, "Failed to update profile: " . $stmt->error);
                }

                // Update session name if changed
                if (!empty($input['name'])) {
                    $_SESSION['name'] = trim((string)$input['name']);
                }
            }

            jsonResponse(true, "Profile updated successfully.", [
                'name' => $_SESSION['name'] ?? ($input['name'] ?? '')
            ]);
            break;

        case "updatePassword":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
            $currentPassword = $input['currentPassword'] ?? ($input['current_password'] ?? '');
            $newPassword = $input['newPassword'] ?? ($input['new_password'] ?? '');

            if (empty($currentPassword) || empty($newPassword)) {
                jsonResponse(false, "Current and new password are required.");
            }

            if (strlen($newPassword) < 6) {
                jsonResponse(false, "New password must be at least 6 characters long.");
            }

            $res = DashboardService::changePassword($conn, $userId, [
                'currentPassword' => $currentPassword,
                'newPassword'     => $newPassword
            ]);

            jsonResponse($res['status'], $res['message']);
            break;

        case "updatePreferences":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

            $toggleFields = [
                'appointment_reminders',
                'message_notifications',
                'health_alerts',
                'newsletter',
                'email_notifications',
                'sms_notifications',
                'two_factor_enabled',
                'share_health_data',
                'theme'
            ];

            $updates = [];
            $types = "";
            $values = [];

            foreach ($toggleFields as $field) {
                if (isset($input[$field]) && in_array($field, $existingColumns, true)) {
                    $updates[] = "`{$field}` = ?";
                    if ($field === 'theme') {
                        $types .= "s";
                        $values[] = (string)$input[$field];
                    } else {
                        $types .= "i";
                        $values[] = (int)$input[$field];
                    }
                }
            }

            if (!empty($updates)) {
                $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
                $types .= "i";
                $values[] = $userId;

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
                if (!$stmt->execute()) {
                    jsonResponse(false, "Failed to save preferences: " . $stmt->error);
                }
            }

            jsonResponse(true, "Preferences saved successfully.");
            break;

        case "deleteAccount":
            // Delete associated appointments if any
            try {
                $delAppt = $conn->prepare("DELETE FROM appointments WHERE patient_id = ?");
                $delAppt->bind_param("i", $userId);
                $delAppt->execute();
            } catch (Exception $ignored) {}

            // Delete user from users table
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            if (!$stmt->execute()) {
                jsonResponse(false, "Failed to delete account. Please try again.");
            }

            // Destroy user session completely
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();

            // Clear remember token
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/');
            }

            jsonResponse(true, "Your account has been permanently deleted.");
            break;

        default:
            jsonResponse(false, "Unknown action requested: " . htmlspecialchars($action));
            break;
    }

} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}
