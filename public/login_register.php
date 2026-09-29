<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Register Logic
    if (isset($_POST['register'])) {

        $usertype = $_POST['usertype'];
        $name     = $_POST['name'];
        $email    = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $gender   = $_POST['gender'];
        $phone    = $_POST['phone'];
        $dob      = $_POST['dob'];

        $address  = $_POST['address'];
        $city     = $_POST['city'];
        $state    = $_POST['state'];
        $zip      = $_POST['zip'];

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

            if ($usertype === "doctor") {

                $stmt = $conn->prepare("
                    INSERT INTO doctors 
                    (user_id, medical_reg_no, specialization, qualification, experience, working_time, about, availability)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $medical_reg_no = $_POST['medical_reg_no'];
                $specialization = $_POST['specialization'];
                $qualification  = $_POST['qualification'];
                $experience     = (int) $_POST['experience'];
                $working_time   = $_POST['working_time'];
                $about          = $_POST['about'];
                $availability   = isset($_POST['availability']) ? 1 : 0;

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

            }

            $conn->commit();

            $_SESSION['register_msg'] = "Registration successful! Please login.";
            header("Location: index.php");
            exit();

        } catch (mysqli_sql_exception $e) {

            $conn->rollback();

            if ($e->getCode() == 1062) {
                $_SESSION['register_msg'] = "Email already registered.";
            } else {
                $_SESSION['register_msg'] = "Registration failed.";
            }

            header("Location: index.php");
            exit();
        }
    }
    // Login Logic
    if (isset($_POST['login'])) {

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("
            SELECT user_id, name, email, password, user_type
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                loginUser($conn, $user);
            }
        }

        $_SESSION['login_error'] = "Incorrect email or password.";
        header("Location: index.php");
        exit();
    }
}

function enableRememberMe(mysqli $conn, array $user) {

    if (!isset($_POST['remember_me'])) return;

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $stmt = $conn->prepare(
        "UPDATE users SET remember_token = ? WHERE user_id = ?"
    );

    $stmt->bind_param("si", $tokenHash, $user['user_id']);
    $stmt->execute();

    setcookie(
        "remember_token",
        $token,
        time() + (30 * 24 * 60 * 60),
        "/",
        "",
        false,
        true
    );
}

function loginUser(mysqli $conn, array $user) {

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['usertype'] = $user['user_type'];

    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("UPDATE users
        SET last_login_at = NOW(),
            last_login_ip = ?
        WHERE user_id = ?");
    $stmt->bind_param("si", $ip, $user['user_id']);
    $stmt->execute();
    $stmt->close();

    enableRememberMe($conn, $user);

    header("Location: /my_doctor/public/{$user['user_type']}/");
    exit();
}

?>
