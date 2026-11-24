<?php
if (
    empty($_SESSION['user_id']) ||
    $_SESSION['usertype'] !== 'doctor'
) {
    header("Location: /my_doctor/public/");
    exit;
}
