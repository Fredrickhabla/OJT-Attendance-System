<?php
// delete_trainee.php

// Connect to your database
$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check that trainee_id was provided
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["trainee_id"])) {
    $trainee_id = trim($_POST["trainee_id"]);

    // Delete trainee record
    $stmt = $conn->prepare("DELETE FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("s", $trainee_id);

    if ($stmt->execute()) {
        // Optionally, delete related records in attendance_record
        $attendanceStmt = $conn->prepare("DELETE FROM attendance_record WHERE trainee_id = ?");
        $attendanceStmt->bind_param("s", $trainee_id);
        $attendanceStmt->execute();
        $attendanceStmt->close();

        // Redirect back to trainee list
        header("Location: trainee.php?message=deleted");
        exit();
    } else {
        echo "Error deleting trainee: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
