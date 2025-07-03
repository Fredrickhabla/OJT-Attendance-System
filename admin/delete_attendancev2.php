<?php
session_start();
include('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid attendance ID.");
}

$id = $_GET['id'];

// Optional: fetch the signature filename to delete the image file too
$stmt = $pdo->prepare("SELECT signature_image FROM attendance_records WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Delete the signature file if it exists
    if (!empty($row['signature']) && file_exists(__DIR__ . "/{$row['signature']}")) {
        unlink(__DIR__ . "/{$row['signature']}");
    }

    // Delete the attendance record
    $delete = $pdo->prepare("DELETE FROM attendance_records WHERE id = ?");
    $delete->execute([$id]);

    // Redirect back to view page
    header("Location: view_attendancev2.php");
    exit;
} else {
    echo "Attendance record not found.";
}
?>
