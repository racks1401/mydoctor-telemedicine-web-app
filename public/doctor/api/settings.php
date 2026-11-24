<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/models/UserModel.php";

try {
    $userId = $_SESSION['user_id'] ?? null;
    $email = $_SESSION['email'] ?? null;
    $userRole = strtolower($_SESSION['usertype'] ?? '');

    if ((!$userId && !$email) || $userRole !== 'doctor') {
        jsonResponse(false, "Unauthorized access. Doctor login required.");
    }

    // Resolve user_id if needed
    if (!$userId && $email) {
        $s = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $s->bind_param("s", $email);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        $userId = $r['user_id'] ?? null;
    }

    if (!$userId) {
        jsonResponse(false, "Doctor account not found.");
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Inspect columns in users and doctors tables dynamically
    function getTableCols(mysqli $conn, string $tbl): array {
        $cols = [];
        $res = $conn->query("SHOW COLUMNS FROM `{$tbl}`");
        if ($res) {
            while ($c = $res->fetch_assoc()) {
                $cols[] = $c['Field'];
            }
        }
        return $cols;
    }

    $userCols = getTableCols($conn, 'users');
    $doctorCols = getTableCols($conn, 'doctors');

    switch ($action) {
        case "getSettings":
            // Fetch users row
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $userRow = $stmt->get_result()->fetch_assoc() ?: [];
            unset($userRow['password']);

            // Fetch doctors row
            $stmt2 = $conn->prepare("SELECT * FROM doctors WHERE user_id = ?");
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $docRow = $stmt2->get_result()->fetch_assoc() ?: [];

            // Fetch recurring schedule from doctor_availability
            $stmtAvail = $conn->prepare("SELECT day_of_week, start_time, end_time, slot_duration, is_available FROM doctor_availability WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time ASC");
            $stmtAvail->bind_param("i", $userId);
            $stmtAvail->execute();
            $resAvail = $stmtAvail->get_result();

            $weeklySchedule = [
                'sun' => [],
                'mon' => [],
                'tue' => [],
                'wed' => [],
                'thu' => [],
                'fri' => [],
                'sat' => []
            ];

            $slotDuration = 30;
            $hasAnyAvailability = false;

            $dayKeyMap = [
                'Sunday'    => 'sun',
                'Monday'    => 'mon',
                'Tuesday'   => 'tue',
                'Wednesday' => 'wed',
                'Thursday'  => 'thu',
                'Friday'    => 'fri',
                'Saturday'  => 'sat'
            ];

            while ($arow = $resAvail->fetch_assoc()) {
                $hasAnyAvailability = true;
                if (!empty($arow['slot_duration'])) {
                    $slotDuration = (int)$arow['slot_duration'];
                }
                if ((int)$arow['is_available'] === 1 && isset($dayKeyMap[$arow['day_of_week']])) {
                    $dk = $dayKeyMap[$arow['day_of_week']];
                    $weeklySchedule[$dk][] = [
                        'start' => substr($arow['start_time'], 0, 5),
                        'end'   => substr($arow['end_time'], 0, 5)
                    ];
                }
            }

            // Merge with defaults for full dynamic safety
            $defaults = [
                'name'                  => $userRow['name'] ?? '',
                'email'                 => $userRow['email'] ?? '',
                'phone'                 => $userRow['phone'] ?? '',
                'dob'                   => $userRow['dob'] ?? '',
                'gender'                => $userRow['gender'] ?? 'Male',
                'address'               => $userRow['address'] ?? '',
                'city'                  => $userRow['city'] ?? '',
                'state'                 => $userRow['state'] ?? '',
                'pin'                   => $userRow['pin'] ?? '',
                'profile_url'           => $userRow['profile_url'] ?? null,
                'created_at'            => $userRow['created_at'] ?? '',
                'last_login_at'         => $userRow['last_login_at'] ?? date('Y-m-d H:i:s'),
                'last_login_ip'         => $userRow['last_login_ip'] ?? '127.0.0.1',
                'specialization'        => $docRow['specialization'] ?? '',
                'medical_reg_no'        => $docRow['medical_reg_no'] ?? '',
                'qualification'         => $docRow['qualification'] ?? '',
                'experience'            => $docRow['experience'] ?? '',
                'working_time'          => $docRow['working_time'] ?? '09:00 AM - 05:00 PM',
                'weekly_schedule'       => $hasAnyAvailability ? $weeklySchedule : null,
                'slot_duration'         => (string)$slotDuration,
                'about'                 => $docRow['about'] ?? '',
                'availability'          => isset($docRow['availability']) ? (int)$docRow['availability'] : 1,
                'two_factor_enabled'    => isset($userRow['two_factor_enabled']) ? (int)$userRow['two_factor_enabled'] : 0,
                'appointment_reminders' => isset($userRow['appointment_reminders']) ? (int)$userRow['appointment_reminders'] : 1,
                'message_notifications' => isset($userRow['message_notifications']) ? (int)$userRow['message_notifications'] : 1,
                'health_alerts'         => isset($userRow['health_alerts']) ? (int)$userRow['health_alerts'] : 1,
                'email_notifications'   => isset($userRow['email_notifications']) ? (int)$userRow['email_notifications'] : 1,
                'sms_notifications'     => isset($userRow['sms_notifications']) ? (int)$userRow['sms_notifications'] : 0,
                'directory_visibility'  => isset($userRow['directory_visibility']) ? (int)$userRow['directory_visibility'] : 1,
                'share_contact_data'    => isset($userRow['share_contact_data']) ? (int)$userRow['share_contact_data'] : 1
            ];

            // Fetch upcoming exceptions from doctor_availability_exceptions
            $stmtExc = $conn->prepare("SELECT exception_id, doctor_id, exception_date, start_time, end_time, slot_duration, is_available, reason, created_at FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date >= CURDATE() ORDER BY exception_date ASC, start_time ASC");
            $stmtExc->bind_param("i", $userId);
            $stmtExc->execute();
            $resExc = $stmtExc->get_result();
            $exceptionsList = [];
            while ($ex = $resExc->fetch_assoc()) {
                $exceptionsList[] = $ex;
            }

            $response = array_merge($defaults, $userRow, $docRow);
            $response['exceptions'] = $exceptionsList;
            if ($hasAnyAvailability) {
                $response['weekly_schedule'] = $weeklySchedule;
                $response['slot_duration'] = (string)$slotDuration;
            }

            jsonResponse(true, "Doctor settings loaded", $response);
            break;

        case "updateProfile":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

            // 1. Update users table fields
            $allowedUserFields = ['name', 'phone', 'dob', 'gender', 'address', 'city', 'state', 'pin'];
            $userUpdates = [];
            $userTypes = "";
            $userValues = [];

            foreach ($allowedUserFields as $f) {
                if (isset($input[$f]) && in_array($f, $userCols, true)) {
                    $userUpdates[] = "`{$f}` = ?";
                    $userTypes .= "s";
                    $userValues[] = trim((string)$input[$f]);
                }
            }

            if (!empty($userUpdates)) {
                $sql = "UPDATE users SET " . implode(", ", $userUpdates) . " WHERE user_id = ?";
                $userTypes .= "i";
                $userValues[] = $userId;
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($userTypes, ...$userValues);
                $stmt->execute();

                if (!empty($input['name'])) {
                    $_SESSION['name'] = trim((string)$input['name']);
                }
            }

            // 2. Update doctors table fields
            $allowedDocFields = ['specialization', 'medical_reg_no', 'qualification', 'experience', 'about'];
            $docUpdates = [];
            $docTypes = "";
            $docValues = [];

            foreach ($allowedDocFields as $f) {
                if (isset($input[$f]) && in_array($f, $doctorCols, true)) {
                    $docUpdates[] = "`{$f}` = ?";
                    $docTypes .= ($f === 'experience') ? "i" : "s";
                    $docValues[] = ($f === 'experience') ? (int)$input[$f] : trim((string)$input[$f]);
                }
            }

            if (!empty($docUpdates)) {
                $chk = $conn->prepare("SELECT user_id FROM doctors WHERE user_id = ?");
                $chk->bind_param("i", $userId);
                $chk->execute();
                $exists = $chk->get_result()->num_rows > 0;

                if ($exists) {
                    $sql = "UPDATE doctors SET " . implode(", ", $docUpdates) . " WHERE user_id = ?";
                    $docTypes .= "i";
                    $docValues[] = $userId;
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($docTypes, ...$docValues);
                    $stmt->execute();
                } else {
                    $fields = array_keys(array_filter($input, fn($k) => in_array($k, $allowedDocFields, true), ARRAY_FILTER_USE_KEY));
                    $fields[] = 'user_id';
                    $placeholders = implode(", ", array_fill(0, count($fields), "?"));
                    $types = str_repeat("s", count($fields) - 1) . "i";
                    $vals = [];
                    foreach ($fields as $fld) {
                        if ($fld === 'user_id') $vals[] = $userId;
                        else $vals[] = $input[$fld] ?? '';
                    }
                    $sql = "INSERT INTO doctors (`" . implode("`, `", $fields) . "`) VALUES ({$placeholders})";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$vals);
                    $stmt->execute();
                }
            }

            jsonResponse(true, "Doctor profile updated successfully.", [
                'name' => $_SESSION['name'] ?? ($input['name'] ?? '')
            ]);
            break;

        case "updateAvailability":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
            $workingTime = $input['working_time'] ?? '09:00 AM - 05:00 PM';
            $availability = isset($input['availability']) ? (int)$input['availability'] : 1;
            $slotDuration = isset($input['slot_duration']) ? max(10, min(120, (int)$input['slot_duration'])) : 30;
            $weeklySchedule = $input['weekly_schedule'] ?? [];
            $rawModes = $input['available_mode'] ?? $input['available_modes'] ?? null;
            if (is_array($rawModes)) {
                $availableModeStr = implode(',', array_filter($rawModes));
            } elseif (is_string($rawModes) && trim($rawModes) !== '') {
                $availableModeStr = trim($rawModes);
            } else {
                $availableModeStr = 'video_call';
            }

            if (is_string($weeklySchedule)) {
                $weeklySchedule = json_decode($weeklySchedule, true) ?: [];
            }

            $conn->begin_transaction();
            try {
                // 1. Delete previous weekly availability records for this doctor
                $del = $conn->prepare("DELETE FROM doctor_availability WHERE doctor_id = ?");
                $del->bind_param("i", $userId);
                $del->execute();

                // 2. Insert new weekly schedule into doctor_availability
                $dayMap = [
                    'sun' => 'Sunday',
                    'mon' => 'Monday',
                    'tue' => 'Tuesday',
                    'wed' => 'Wednesday',
                    'thu' => 'Thursday',
                    'fri' => 'Friday',
                    'sat' => 'Saturday'
                ];

                $insAvail = $conn->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration, is_available) VALUES (?, ?, ?, ?, ?, 1)");

                foreach ($dayMap as $key => $dayName) {
                    if (!empty($weeklySchedule[$key]) && is_array($weeklySchedule[$key])) {
                        $seenSlots = [];
                        foreach ($weeklySchedule[$key] as $slot) {
                            $startStr = trim($slot['start'] ?? '');
                            $endStr = trim($slot['end'] ?? '');
                            if ($startStr && $endStr) {
                                $sTime = date('H:i:s', strtotime($startStr));
                                $eTime = date('H:i:s', strtotime($endStr));

                                if (($sTime === '00:00:00' && $eTime === '00:00:00') || $sTime >= $eTime) {
                                    throw new Exception("Please set valid working hours for {$dayName} (start time must be earlier than end time).");
                                }

                                $slotKey = "{$sTime}_{$eTime}";
                                if (isset($seenSlots[$slotKey])) {
                                    throw new Exception("Time slot (" . date('h:i A', strtotime($sTime)) . " - " . date('h:i A', strtotime($eTime)) . ") is already present for {$dayName}.");
                                }

                                foreach ($seenSlots as $existingKey => $existing) {
                                    if ($sTime < $existing['end'] && $eTime > $existing['start']) {
                                        throw new Exception("Time slots overlap on {$dayName} (" . date('h:i A', strtotime($sTime)) . " - " . date('h:i A', strtotime($eTime)) . ").");
                                    }
                                }

                                $seenSlots[$slotKey] = ['start' => $sTime, 'end' => $eTime];

                                $insAvail->bind_param("isssi", $userId, $dayName, $sTime, $eTime, $slotDuration);
                                $insAvail->execute();
                            }
                        }
                    }
                }

                // 3. Update doctors table (working_time summary and availability status)
                $chk = $conn->prepare("SELECT user_id FROM doctors WHERE user_id = ?");
                $chk->bind_param("i", $userId);
                $chk->execute();
                $exists = $chk->get_result()->num_rows > 0;

                if ($exists) {
                    $upDoc = $conn->prepare("UPDATE doctors SET working_time = ?, availability = ?, available_mode = ? WHERE user_id = ?");
                    $upDoc->bind_param("sisi", $workingTime, $availability, $availableModeStr, $userId);
                    $upDoc->execute();
                } else {
                    $insDoc = $conn->prepare("INSERT INTO doctors (user_id, working_time, availability, available_mode) VALUES (?, ?, ?, ?)");
                    $insDoc->bind_param("isis", $userId, $workingTime, $availability, $availableModeStr);
                    $insDoc->execute();
                }

                // 4. Clean up any leftover unbooked available slots for this doctor
                // Only delete unbooked available slots so booked appointments and leaves are preserved
                $delSlots = $conn->prepare("DELETE FROM doctor_slots WHERE doctor_id = ? AND (appointment_id IS NULL AND status = 'available')");
                $delSlots->bind_param("i", $userId);
                $delSlots->execute();
                $delSlots->close();

                $conn->commit();
                jsonResponse(true, "Clinic schedule and availability saved successfully.");
            } catch (Exception $e) {
                $conn->rollback();
                jsonResponse(false, "Failed to save schedule: " . $e->getMessage());
            }
            break;

        case "updateAvailableModes":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
            $rawModes = $input['available_mode'] ?? $input['available_modes'] ?? null;
            if (is_array($rawModes)) {
                $availableModeStr = implode(',', array_filter($rawModes));
            } elseif (is_string($rawModes) && trim($rawModes) !== '') {
                $availableModeStr = trim($rawModes);
            } else {
                $availableModeStr = 'video_call';
            }

            $chk = $conn->prepare("SELECT user_id FROM doctors WHERE user_id = ?");
            $chk->bind_param("i", $userId);
            $chk->execute();
            $exists = $chk->get_result()->num_rows > 0;

            if ($exists) {
                $upDoc = $conn->prepare("UPDATE doctors SET available_mode = ? WHERE user_id = ?");
                $upDoc->bind_param("si", $availableModeStr, $userId);
                $upDoc->execute();
            } else {
                $insDoc = $conn->prepare("INSERT INTO doctors (user_id, available_mode) VALUES (?, ?)");
                $insDoc->bind_param("is", $userId, $availableModeStr);
                $insDoc->execute();
            }

            jsonResponse(true, "Available consultation modes saved successfully!", [
                'available_mode' => $availableModeStr
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

            $res = UserModel::changePassword($conn, $userId, [
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
                'email_notifications',
                'sms_notifications',
                'two_factor_enabled',
                'directory_visibility',
                'share_contact_data'
            ];

            $updates = [];
            $types = "";
            $values = [];

            foreach ($toggleFields as $field) {
                if (isset($input[$field]) && in_array($field, $userCols, true)) {
                    $updates[] = "`{$field}` = ?";
                    $types .= "i";
                    $values[] = (int)$input[$field];
                }
            }

            if (!empty($updates)) {
                $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
                $types .= "i";
                $values[] = $userId;

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
            }

            jsonResponse(true, "Preferences saved successfully.");
            break;

        case "uploadPhoto":
            if (!isset($_FILES['profile_pic'])) {
                jsonResponse(false, "No photo uploaded.");
            }

            $file = $_FILES['profile_pic'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                jsonResponse(false, "Upload error code: " . $file['error']);
            }

            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                jsonResponse(false, "Invalid image format. Allowed: JPG, PNG, GIF, WebP.");
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                jsonResponse(false, "Image size exceeds 2MB limit.");
            }

            // Make sure the file is really an image (not a renamed script)
            if (@getimagesize($file['tmp_name']) === false) {
                jsonResponse(false, "The uploaded file is not a valid image.");
            }

            $uploadDir = __DIR__ . '/../../../public/uploads/profile/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = 'doc_' . $userId . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;
            $webUrl = '/my_doctor/public/uploads/profile/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("UPDATE users SET profile_url = ? WHERE user_id = ?");
                $stmt->bind_param("si", $webUrl, $userId);
                $stmt->execute();

                jsonResponse(true, "Profile photo updated successfully.", [
                    'profile_url' => $webUrl
                ]);
            } else {
                jsonResponse(false, "Failed to save uploaded image.");
            }
            break;

        case "deleteAccount":
            // Delete associated appointments if any
            try {
                // Delete where doctor is assigned or booked
                $chkDoctor = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
                $chkDoctor->bind_param("i", $userId);
                $chkDoctor->execute();
                $docRes = $chkDoctor->get_result()->fetch_assoc();
                $docTableId = $docRes['doctor_id'] ?? null;

                if ($docTableId) {
                    $delAppt = $conn->prepare("DELETE FROM appointments WHERE doctor_id = ?");
                    $delAppt->bind_param("i", $docTableId);
                    $delAppt->execute();
                }

                // Delete from doctors table
                $delDoc = $conn->prepare("DELETE FROM doctors WHERE user_id = ?");
                $delDoc->bind_param("i", $userId);
                $delDoc->execute();
            } catch (Exception $ignored) {}

            // Delete user from users table
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            if (!$stmt->execute()) {
                jsonResponse(false, "Failed to delete doctor account. Please try again.");
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

            jsonResponse(true, "Your doctor account has been permanently deleted.");
            break;

                case "getExceptions":
            $stmt = $conn->prepare("SELECT exception_id, doctor_id, exception_date, start_time, end_time, slot_duration, is_available, reason, created_at FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date >= CURDATE() ORDER BY exception_date ASC, start_time ASC");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $exceptions = [];
            while ($r = $res->fetch_assoc()) {
                $exceptions[] = $r;
            }
            jsonResponse(true, "Exceptions fetched", ['exceptions' => $exceptions]);
            break;

        case "saveException":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
            $date = trim($input['exception_date'] ?? '');
            $isAvailable = isset($input['is_available']) ? (int)$input['is_available'] : 0;
            $reason = trim($input['reason'] ?? '');
            $slots = $input['slots'] ?? [];
            $force = !empty($input['force']);

            if (empty($date)) {
                jsonResponse(false, "Please select a date for the exception/leave.");
            }
            if ($date < date('Y-m-d')) {
                jsonResponse(false, "Cannot set exception or leave for a past date.");
            }
            $maxDate = date('Y-m-d', strtotime('+60 days'));
            if ($date > $maxDate) {
                jsonResponse(false, "Date cannot be more than 2 months ahead.");
            }

            // Conflict check for booked appointments on this date
            $stmtConf = $conn->prepare("SELECT COUNT(*) AS booked_count, GROUP_CONCAT(CONCAT(TIME_FORMAT(appointment_time, '%h:%i %p'), ' (Appt #', appointment_id, ')') SEPARATOR ', ') AS details FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status IN ('confirmed', 'pending')");
            $stmtConf->bind_param("is", $userId, $date);
            $stmtConf->execute();
            $confRow = $stmtConf->get_result()->fetch_assoc();
            $bookedCount = (int)($confRow['booked_count'] ?? 0);

            if ($bookedCount > 0 && $isAvailable === 0 && !$force) {
                jsonResponse(false, "Conflict detected: You have {$bookedCount} active booked appointment(s) on {$date} ({$confRow['details']}). Taking a full day leave will conflict with these appointments.", [
                    'has_conflict' => true,
                    'booked_count' => $bookedCount,
                    'details'      => $confRow['details']
                ]);
            }

            // Delete existing exceptions for this date
            $delStmt = $conn->prepare("DELETE FROM doctor_availability_exceptions WHERE doctor_id = ? AND exception_date = ?");
            $delStmt->bind_param("is", $userId, $date);
            $delStmt->execute();

            if ($isAvailable === 0) {
                // Full Day Leave
                $insStmt = $conn->prepare("INSERT INTO doctor_availability_exceptions (doctor_id, exception_date, start_time, end_time, slot_duration, is_available, reason) VALUES (?, ?, NULL, NULL, NULL, 0, ?)");
                $insStmt->bind_param("iss", $userId, $date, $reason);
                $insStmt->execute();
            } else {
                // Half day / Custom timing slots
                if (empty($slots) || !is_array($slots)) {
                    jsonResponse(false, "Please specify at least one time slot for custom working hours.");
                }
                $insStmt = $conn->prepare("INSERT INTO doctor_availability_exceptions (doctor_id, exception_date, start_time, end_time, slot_duration, is_available, reason) VALUES (?, ?, ?, ?, ?, 1, ?)");
                $slotDuration = (int)($input['slot_duration'] ?? 30);
                foreach ($slots as $slot) {
                    $st = trim($slot['start'] ?? '');
                    $et = trim($slot['end'] ?? '');
                    if (!empty($st) && !empty($et)) {
                        $startTime = strlen($st) === 5 ? $st . ":00" : $st;
                        $endTime = strlen($et) === 5 ? $et . ":00" : $et;
                        $insStmt->bind_param("isssis", $userId, $date, $startTime, $endTime, $slotDuration, $reason);
                        $insStmt->execute();
                    }
                }
            }

            jsonResponse(true, "Schedule exception saved successfully!");
            break;

        case "deleteException":
            $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
            $exceptionId = (int)($input['exception_id'] ?? 0);
            if ($exceptionId <= 0) {
                jsonResponse(false, "Invalid exception ID");
            }
            $stmt = $conn->prepare("DELETE FROM doctor_availability_exceptions WHERE exception_id = ? AND doctor_id = ?");
            $stmt->bind_param("ii", $exceptionId, $userId);
            $stmt->execute();
            jsonResponse(true, "Schedule exception removed successfully!");
            break;
        default:
            jsonResponse(false, "Invalid action requested.");
            break;
    }

} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}
