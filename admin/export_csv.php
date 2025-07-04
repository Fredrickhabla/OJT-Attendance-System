<?php
session_start();
include('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: indexv2.php");
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_record.csv');

$output = fopen('php://output', 'w');

// CSV column headers
fputcsv($output, ['Attendance ID', 'Trainee ID', 'Date', 'Time In', 'Time Out', 'Hours', 'Work Description', 'Signature', 'Status']);

// Fetch records
$stmt = $pdo->query("SELECT * FROM attendance_record ORDER BY attendance_id DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['attendance_id'],
        $row['trainee_id'],
        $row['date'],
        $row['time_in'],
        $row['time_out'],
        $row['hours'],
        $row['work_description'],
        $row['signature'],
        $row['status']
    ]);
}

fclose($output);
exit;
