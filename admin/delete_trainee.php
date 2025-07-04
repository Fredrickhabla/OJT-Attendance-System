<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

// Create DB connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$trainee_id = $_GET['id'] ?? '';

if (!empty($trainee_id)) {
    // First, delete related records (e.g., attendance) if necessary
    $conn->query("DELETE FROM attendance_record WHERE trainee_id = '$trainee_id'");

    // Delete the trainee record
    $deleteTrainee = $conn->prepare("DELETE FROM trainee WHERE trainee_id = ?");
    $deleteTrainee->bind_param("s", $trainee_id);

    if ($deleteTrainee->execute()) {
        echo "<script>alert('Trainee deleted successfully.'); window.location.href='trainee.php';</script>";
    } else {
        echo "<script>alert('Failed to delete trainee.'); window.history.back();</script>";
    }
    $deleteTrainee->close();
} else {
    echo "<script>alert('Invalid trainee ID.'); window.history.back();</script>";
}

$conn->close();
?>
