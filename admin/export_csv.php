<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_records.csv');

$output = fopen('php://output', 'w');

// CSV column headers
fputcsv($output, ['Date', 'Morning In', 'Morning Out', 'Afternoon In', 'Afternoon Out', 'Hours', 'Work Description']);

// Fetch records
$stmt = $pdo->query("SELECT * FROM attendance_records ORDER BY date DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['date'],
        $row['morning_in'],
        $row['morning_out'],
        $row['afternoon_in'],
        $row['afternoon_out'],
        $row['hours'],
        $row['work_description']
    ]);
}

fclose($output);
exit;
