<?php
session_start();
include('../conn.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: indexv2.php");
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_record.csv');

$output = fopen('php://output', 'w');


fputcsv($output, ['Name', 'Date', 'Time In', 'Time Out', 'Hours', 'Hours Late', 'Status']);


$sql = "
    SELECT 
        ar.date, ar.time_in, ar.time_out, ar.hours, ar.hours_late, ar.status,
        CONCAT(t.first_name, ' ', t.surname) AS full_name
    FROM 
        attendance_record ar
    JOIN 
        trainee t ON ar.trainee_id = t.trainee_id
    ORDER BY 
        ar.attendance_id DESC
";

$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['full_name'],
        $row['date'],
        $row['time_in'],
        $row['time_out'],
        $row['hours'],
        $row['hours_late'],
        $row['status']
    ]);
}

fclose($output);
exit;
