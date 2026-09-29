<?php
if (
    empty($_SESSION['user_id']) ||
    $_SESSION['usertype'] !== 'admin'
) {
    header("Location: /my_doctor/public/");
    exit;
}