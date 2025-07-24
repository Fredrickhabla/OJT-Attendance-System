<?php
session_start();
include('../conn.php');
require_once '../logger.php'; // ✅ Include logger functions

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

// Fetch the record for logging before deletion
$stmt = $pdo->prepare("SELECT * FROM attendance_record WHERE attendance_id = ?");
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
$success = $delete->execute([$attendance_id]);

// Logging (only if deletion is successful)
if ($success) {
    $admin_id = $_SESSION['user_id'] ?? 'unknown';
    $admin_name = $_SESSION['fullname'] ?? 'Unknown Admin';
    $user_id = $record['user_id'];

    // Transaction log
    logTransaction($pdo, $admin_id, $admin_name, "Deleted attendance record ID: $attendance_id", $admin_name);

    // Audit log
    $old_values = json_encode([
        'date' => $record['date'],
        'time_in' => $record['time_in'],
        'time_out' => $record['time_out'],
        'hours' => $record['hours'],
        'work_description' => $record['work_description'],
        'signature' => $record['signature']
    ]);
    $new_values = '-';

    logAudit($pdo, $user_id, "Delete Attendance Record: $attendance_id", $new_values, $old_values, $admin_name, 'Y');
}

// Redirect back to the attendance view
header("Location: view_attendancev2.php");
exit;
?>
