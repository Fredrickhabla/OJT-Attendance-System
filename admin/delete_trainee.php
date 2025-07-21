<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$trainee_id = $_POST['trainee_id'] ?? '';

if (!empty($trainee_id)) {
    // Archive the trainee (set active to 'N')
    $archiveStmt = $conn->prepare("UPDATE trainee SET active = 'N' WHERE trainee_id = ?");
    $archiveStmt->bind_param("s", $trainee_id);

    if ($archiveStmt->execute()) {
        echo "<script>alert('Trainee archived successfully.'); window.location.href='trainee.php';</script>";
    } else {
        echo "<script>alert('Failed to archive trainee.'); window.history.back();</script>";
    }

    $archiveStmt->close();
} else {
    echo "<script>alert('Invalid trainee ID.'); window.history.back();</script>";
}

$conn->close();
?>
