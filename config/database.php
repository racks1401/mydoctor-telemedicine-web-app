<?php

date_default_timezone_set('Asia/Kolkata');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "my_doctor_db";

$conn = new mysqli($host, $user, $password, $database);

if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

$conn->query("SET time_zone = '+05:30'");
?>
