<?php

include('../connection.php');

if (isset($_GET["coordinator_id"])) {
    $coordinator_id = trim($_GET["coordinator_id"]);

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
