<?php
session_start();
require_once '../connection/config.php';

if (!isset($_SESSION['email']) || $_SESSION['usertype'] !== 'Doctor') {
    header("Location: ../index.php");
    exit();
}

$dr_id = $_SESSION['dr_id'];
$valid_modes = ['Clinic Visit', 'Voice Call', 'Video Call'];
$modes = $_POST['consult_modes'] ?? [];
$charges = $_POST['charges'] ?? [];

// Step 1: Fetch existing modes
$existing_modes = [];
$result = $conn->query("SELECT mode, charge FROM consultation_modes WHERE dr_id = '$dr_id'");
while ($row = $result->fetch_assoc()) {
    $existing_modes[$row['mode']] = $row['charge'];
}

// Step 2: Process new submissions
foreach ($valid_modes as $mode) {
    $isChecked = in_array($mode, $modes);
    $submittedCharge = isset($charges[$mode]) && is_numeric($charges[$mode]) ? intval($charges[$mode]) : 0;

    if ($isChecked) {
        if (!isset($existing_modes[$mode])) {
            // Insert new mode
            $stmt = $conn->prepare("INSERT INTO consultation_modes (dr_id, mode, charge) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $dr_id, $mode, $submittedCharge);
            $stmt->execute();
        } elseif ($existing_modes[$mode] != $submittedCharge) {
            // Update charge if changed
            $stmt = $conn->prepare("UPDATE consultation_modes SET charge = ? WHERE dr_id = ? AND mode = ?");
            $stmt->bind_param("iis", $submittedCharge, $dr_id, $mode);
            $stmt->execute();
        }
    } else {
        if (isset($existing_modes[$mode])) {
            // Delete unchecked mode
            $stmt = $conn->prepare("DELETE FROM consultation_modes WHERE dr_id = ? AND mode = ?");
            $stmt->bind_param("is", $dr_id, $mode);
            $stmt->execute();
        }
    }
}

$_SESSION['message'] = "Consultation modes updated successfully.";
header("Location: update-profile.php");
exit();
?>
