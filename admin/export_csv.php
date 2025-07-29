<?php
session_start();
include('../conn.php');

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: indexv2.php");
    exit;
}

// Get filters
$name = $_GET['name'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Build SQL (no group)
$sql = "
    SELECT DISTINCT ar.*, CONCAT(u.first_name, ' ', u.surname) AS full_name
    FROM attendance_record ar
    LEFT JOIN trainee u ON ar.trainee_id = u.trainee_id
    WHERE 1=1
";

$params = [];

if (!empty($name)) {
    $sql .= " AND CONCAT(u.first_name, ' ', u.surname) = :name";
    $params[':name'] = $name;
}
if (!empty($from_date)) {
    $sql .= " AND ar.date >= :from_date";
    $params[':from_date'] = $from_date;
}
if (!empty($to_date)) {
    $sql .= " AND ar.date <= :to_date";
    $params[':to_date'] = $to_date;
}

// Sort by full name (A–Z), then by date descending
$sql .= " ORDER BY full_name ASC, ar.date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_record.csv');

$output = fopen('php://output', 'w');

// Write header row
fputcsv($output, ['Name', 'Date', 'Time In', 'Time Out', 'Hours', 'Status', 'Hours Late']);

$recorded = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $key = $row['full_name'] . $row['date'] . $row['time_in'] . $row['time_out'];

    if (!in_array($key, $recorded)) {
        $time_in = !empty($row['time_in']) ? date('g:i A', strtotime($row['time_in'])) : '';
        $time_out = !empty($row['time_out']) ? date('g:i A', strtotime($row['time_out'])) : '';
        $hours = is_numeric($row['hours']) ? floatval($row['hours']) : 0.00;
        $hours_late = isset($row['hours_late']) ? $row['hours_late'] : '0.00';

        fputcsv($output, [
            $row['full_name'],
            $row['date'],
            $time_in,
            $time_out,
            number_format($hours, 2),
            $row['status'],
            number_format(floatval($hours_late), 2)
        ]);

        $recorded[] = $key;
    }
}

fclose($output);
exit;
