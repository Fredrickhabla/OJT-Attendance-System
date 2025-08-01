<?php
session_start();


$host = "localhost";
$dbname = "ojtform";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $user_id   = $_SESSION['user_id'];
    $date      = $_POST['date'] ?? '';
    $time_in   = $_POST['time_in'] ?? '';
    $time_out  = $_POST['time_out'] ?? '';
    $hours     = floatval($_POST['hours'] ?? 0);
    $work_desc = $_POST['work_description'] ?? '';

    
    if (empty($date) || empty($time_in) || empty($time_out) || empty($hours) || empty($work_desc)) {
        die("All fields are required.");
    }

 
    $signature_path = '';
    if (!empty($_FILES['signature']['name'])) {
        if ($_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['signature']['tmp_name'];
            $originalName = basename($_FILES['signature']['name']);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);

            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uniqueFilename = uniqid("sig_") . "." . $ext;
            $destination = $uploadDir . $uniqueFilename;

            if (move_uploaded_file($tmpName, $destination)) {
                $signature_path = $destination;
            } else {
                die("Failed to upload signature image.");
            }
        } else {
            die("Signature upload error.");
        }
    }

   
    $stmt = $conn->prepare("
        INSERT INTO attendance_records 
        (user_id, date, time_in, time_out, hours, work_description, signature, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssdss",
        $user_id,
        $date,
        $time_in,
        $time_out,
        $hours,
        $work_desc,
        $signature_path
    );

    if ($stmt->execute()) {
        header("Location: success.php");
        exit();
    } else {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
