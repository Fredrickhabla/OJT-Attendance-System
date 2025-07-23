<?php
session_start();
include('../conn.php');

// Only admin can delete
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

// Validate parameter
if (!isset($_GET['attendance_id']) || empty($_GET['attendance_id'])) {
    die("No attendance ID provided.");
}

$attendance_id = trim($_GET['attendance_id']); // e.g., attendance_20250624_5

// Use the correct table name here (e.g., `attendance`)
$stmt = $pdo->prepare("SELECT signature FROM attendance_record WHERE attendance_id = ?");
$stmt->execute([$attendance_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    echo "Attendance record not found.";
    exit;
}

// Optionally delete the signature file if it exists
if (!empty($record['signature'])) {
    $signatureFile = __DIR__ . "/" . $record['signature'];
    if (file_exists($signatureFile)) {
        unlink($signatureFile);
    }
}

// Delete the attendance record
$delete = $pdo->prepare("DELETE FROM attendance_record WHERE attendance_id = ?");
$delete->execute([$attendance_id]);

// Redirect back to the attendance view
header("Location: view_attendancev2.php");
exit;
?>
