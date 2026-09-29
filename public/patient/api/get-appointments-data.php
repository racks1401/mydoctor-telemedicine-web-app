<?php
include '../config.php'; // make sure path is correct

$sql = "
    SELECT DATE(appointment_date) AS day, COUNT(*) AS total
    FROM appointments
    WHERE appointment_date >= CURDATE() - INTERVAL 9 DAY
    GROUP BY day
    ORDER BY day ASC
";

$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "label" => date("M j", strtotime($row["day"])),
            "value" => (int)$row["total"]
        ];
    }
}

echo json_encode($data);
?>
