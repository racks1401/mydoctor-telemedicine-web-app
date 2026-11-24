<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// ======================================================
// REGISTER
// ======================================================
if (isset($_POST['register'])) {

    // Only doctors and patients can self-register.
    // Admin accounts must be created directly in the database by an existing admin.
    $usertype = strtolower(trim($_POST['usertype'] ?? ''));
    if (!in_array($usertype, ['doctor', 'patient'], true)) {
        failRegister("Invalid account type.");
    }

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $rawPass  = (string)($_POST['password'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $dob      = trim($_POST['dob'] ?? '') ?: null;
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $state    = trim($_POST['state'] ?? '');
    $zip      = trim($_POST['zip'] ?? '');

    $gender = strtolower(trim($_POST['gender'] ?? ''));
    if (!in_array($gender, ['male', 'female', 'other'], true)) {
        $gender = null;
    }

    if ($name === '' || $email === '' || $phone === '' || $rawPass === '') {
        failRegister("Name, email, phone and password are required.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        failRegister("Please enter a valid email address.");
    }
    if (strlen($rawPass) < 6) {
        failRegister("Password must be at least 6 characters.");
    }

    $password = password_hash($rawPass, PASSWORD_DEFAULT);

    if ($usertype === 'doctor') {
        $medical_reg_no = trim($_POST['medical_reg_no'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        if ($medical_reg_no === '' || $specialization === '') {
            failRegister("Specialization and license number are required for doctors.");
        }
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            INSERT INTO users
            (user_type, name, email, password, phone, gender, dob, address, city, state, pin, privacy_accepted, privacy_accepted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->bind_param(
            "sssssssssss",
            $usertype, $name, $email, $password,
            $phone, $gender, $dob,
            $address, $city, $state, $zip
        );
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($usertype === 'doctor') {
            $stmt = $conn->prepare("
                INSERT INTO doctors
                (user_id, medical_reg_no, specialization, qualification, experience, working_time, about, availability)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $qualification = trim($_POST['qualification'] ?? '');
            $experience    = (int)($_POST['experience'] ?? 0);
            $working_time  = trim($_POST['working_time'] ?? '');
            $about         = trim($_POST['about'] ?? '');
            $availability  = isset($_POST['availability']) ? 1 : 0;

            $stmt->bind_param(
                "isssissi",
                $userId,
                $medical_reg_no,
                $specialization,
                $qualification,
                $experience,
                $working_time,
                $about,
                $availability
            );
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        $_SESSION['register_msg'] = "Registration successful! Please login.";
        header("Location: index.php");
        exit();

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        if ($e->getCode() == 1062) {
            failRegister("This email or license number is already registered.");
        }
        error_log("Registration error: " . $e->getMessage());
        failRegister("Registration failed. Please try again.");
    }
}

// ======================================================
// LOGIN
// ======================================================
if (isset($_POST['login'])) {

    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $stmt = $conn->prepare("
        SELECT user_id, name, email, password, user_type, status
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        if (($user['status'] ?? 'active') === 'blocked') {
            $_SESSION['login_error'] = "Your account has been blocked. Please contact support.";
            header("Location: index.php");
            exit();
        }
        loginUser($conn, $user);
    }

    $_SESSION['login_error'] = "Incorrect email or password.";
    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();

// ======================================================
// HELPERS
// ======================================================

function failRegister(string $message): void {
    $_SESSION['register_error'] = $message;
    header("Location: index.php");
    exit();
}

function enableRememberMe(mysqli $conn, array $user): void {

    if (empty($_POST['remember_me'])) return;

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE user_id = ?");
    $stmt->bind_param("si", $tokenHash, $user['user_id']);
    $stmt->execute();
    $stmt->close();

    setcookie('remember_token', $token, [
        'expires'  => time() + (30 * 24 * 60 * 60),
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function loginUser(mysqli $conn, array $user): void {

    // New session id on login prevents session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']  = (int)$user['user_id'];
    $_SESSION['name']     = $user['name'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['usertype'] = $user['user_type'];

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $conn->prepare("
        UPDATE users
        SET last_login_at = NOW(),
            last_login_ip = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("si", $ip, $user['user_id']);
    $stmt->execute();
    $stmt->close();

    enableRememberMe($conn, $user);

    header("Location: /my_doctor/public/{$user['user_type']}/");
    exit();
}
