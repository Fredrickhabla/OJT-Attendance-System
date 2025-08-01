<?php
include('../conn.php');

if (!isset($_GET['start_date']) || !isset($_GET['end_date'])) {
    die("Missing parameters.");
}

$start = $_GET['start_date'];
$end = $_GET['end_date'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="dtr_records.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'Date', 'Time In', 'Time Out', 'Hours', 'Hours Late']);

$stmt = $pdo->prepare("
    SELECT CONCAT(u.first_name, ' ', u.surname) AS full_name, ar.date, ar.time_in, ar.time_out, ar.hours, ar.hours_late
    FROM attendance_record ar
    LEFT JOIN trainee u ON ar.trainee_id = u.trainee_id
    WHERE u.active = 'Y' AND ar.date BETWEEN ? AND ?
    ORDER BY ar.date ASC
");
$stmt->execute([$start, $end]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['full_name'],
        $row['date'],
        $row['time_in'],
        $row['time_out'],
        $row['hours'],
        $row['hours_late']
    ]);
}

fclose($output);
exit;
