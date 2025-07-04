<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['trainee_id'] ?? '';

if (!empty($id)) {
    $stmt = $conn->prepare("DELETE FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

$conn->close();

// Redirect back to trainee list
header("Location: trainee.php");
exit;
?>
