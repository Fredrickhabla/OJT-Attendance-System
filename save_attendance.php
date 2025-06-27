<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: indexv2.php");
    exit();
}

$host = "localhost";
$dbname = "ojtform";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect data
$user_id = $_SESSION['user_id'];
$date = $_POST['date'] ?? '';
$time_in = $_POST['time_in'] ?? '';
$time_out = $_POST['time_out'] ?? '';
$hours = $_POST['hours'] ?? '';
$work_desc = $_POST['work_desc'] ?? '';

// Handle image upload
$signature_path = '';
if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = basename($_FILES['signature']['name']);
    $unique_name = time() . '_' . $filename;
    $target_path = $upload_dir . $unique_name;

    if (move_uploaded_file($_FILES['signature']['tmp_name'], $target_path)) {
        $signature_path = $target_path;
    } else {
        die("❌ Failed to upload signature.");
    }
} else {
    die("❌ No signature image uploaded.");
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO attendance_records (user_id, date, time_in, time_out, hours, work_description, signature, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("issssss", $user_id, $date, $time_in, $time_out, $hours, $work_desc, $signature_path);

if ($stmt->execute()) {
    header("Location: success.php");
    exit();
} else {
    echo "❌ Error saving attendance: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
