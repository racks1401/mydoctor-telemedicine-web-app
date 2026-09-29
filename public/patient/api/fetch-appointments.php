<?php
header('Content-Type: application/json');
include '../connection/config.php'; // use separate file for DB connection

$sql = "SELECT day_name, day_number, title, location, type, 
               TIME_FORMAT(time_from, '%h %p') as time_from, 
               TIME_FORMAT(time_to, '%h %p') as time_to, color 
        FROM events ORDER BY day_number DESC";

$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
  $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>
