<?php
if (
    empty($_SESSION['user_id']) ||
    $_SESSION['usertype'] !== 'patient'
) {
    header("Location: /my_doctor/public/");
    exit;
}
