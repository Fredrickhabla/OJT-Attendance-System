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
fputcsv($output, ['Date', 'Time In', 'Time Out', 'Hours', 'Work Description', 'Signature Image']);

// Fetch records
$stmt = $pdo->query("SELECT * FROM attendance_records ORDER BY date DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $formatted_time_in = date('g:iA', strtotime($row['time_in']));
    $formatted_time_out = date('g:iA', strtotime($row['time_out']));
    
    // If your site is online, use full URL (e.g., https://yourdomain.com/uploads/...)
    $signature_url = 'http://localhost/ojtform/' . $row['signature']; // Adjust path as needed

    fputcsv($output, [
        $row['date'],
        $formatted_time_in,
        $formatted_time_out,
        $row['hours'],
        $row['work_description'],
        $signature_url
    ]);
}

fclose($output);
exit;
