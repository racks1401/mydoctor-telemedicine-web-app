
<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$password = "";
$database = "my_doctor_db";

$conn = new mysqli($host, $user, $password, $database);

if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}
?>