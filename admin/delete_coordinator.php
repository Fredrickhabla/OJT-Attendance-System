<?php
// delete_coordinator.php

$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check that coordinator_id was provided in GET
if (isset($_GET["coordinator_id"])) {
    $coordinator_id = trim($_GET["coordinator_id"]);

    $stmt = $conn->prepare("DELETE FROM coordinator WHERE coordinator_id = ?");
    $stmt->bind_param("s", $coordinator_id);

    if ($stmt->execute()) {
        header("Location: coordinator.php?message=deleted");
        exit();
    } else {
        echo "Error deleting coordinator: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
