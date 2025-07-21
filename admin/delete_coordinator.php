<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET["coordinator_id"])) {
    $coordinator_id = trim($_GET["coordinator_id"]);

    // Change DELETE to UPDATE to set active = 'N'
    $stmt = $conn->prepare("UPDATE coordinator SET active = 'N' WHERE coordinator_id = ?");
    $stmt->bind_param("s", $coordinator_id);

    if ($stmt->execute()) {
        header("Location: coordinator.php?message=deactivated");
        exit();
    } else {
        echo "Error deactivating coordinator: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
