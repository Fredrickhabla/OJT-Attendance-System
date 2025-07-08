<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'ojtformv3');
    if ($conn->connect_error) die("Connection failed");

    $id = $_POST['coordinator_id'] ?? '';
    $position = $_POST['position'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $imagePath = '';
    if (!empty($_FILES['profile_picture']['name'])) {
        $filename = basename($_FILES['profile_picture']['name']);
        $targetPath = "admin/uploads/" . $filename;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath);
        $imagePath = $targetPath;
    }

    if ($imagePath) {
        $query = "UPDATE coordinator SET position=?, email=?, phone=?, profile_picture=? WHERE coordinator_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $position, $email, $phone, $imagePath, $id);
    } else {
        $query = "UPDATE coordinator SET position=?, email=?, phone=? WHERE coordinator_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $position, $email, $phone, $id);
    }

    if ($stmt->execute()) {
        // Optional: check if any rows were actually affected
        if ($stmt->affected_rows > 0) {
            header("Location: coordinator.php");
            exit();
        } else {
            echo "No changes made (same data or invalid ID).";
        }
    } else {
        echo "Query error: " . $stmt->error;
    }
}
?>
