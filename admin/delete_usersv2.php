<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin'])) {
    header("Location: /ojtform/index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: view_attendancev2.php");
    exit;
}

// Fetch the record to get the signature file path
$stmt = $pdo->prepare("SELECT signature FROM attendance_records WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    echo "Attendance record not found.";
    exit;
}

// Delete the signature file if it exists
if (!empty($record['signature']) && file_exists($record['signature'])) {
    unlink($record['signature']);
}

// Delete the attendance record
$delete = $pdo->prepare("DELETE FROM attendance_records WHERE id = ?");
$delete->execute([$id]);

header("Location: view_attendancev2.php");
exit;
?>
