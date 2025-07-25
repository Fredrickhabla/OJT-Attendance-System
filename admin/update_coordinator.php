<?php
session_start();
require_once 'logger.php';

include('../connection.php');
include('../conn.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['coordinator_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';


    $oldData = [];
    $result = $conn->query("SELECT name, position, email, phone, profile_picture FROM coordinator WHERE coordinator_id = '$id'");
    if ($result && $result->num_rows > 0) {
        $oldData = $result->fetch_assoc();
    }

    $imagePath = $oldData['profile_picture'] ?? '';
    if (!empty($_FILES['profile_picture']['name'])) {
        $filename = basename($_FILES['profile_picture']['name']);
        $uploadDir = "../uploads/";
        $publicPath = "uploads/" . $filename;
        $serverPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $serverPath)) {
            $imagePath = $publicPath;
        }
    }

    // Update query
    if (!empty($_FILES['profile_picture']['name'])) {
        $query = "UPDATE coordinator SET name=?, position=?, email=?, phone=?, profile_picture=? WHERE coordinator_id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $name, $position, $email, $phone, $imagePath, $id);

    } else {
        $query = "UPDATE coordinator SET name=?, position=?, email=?, phone=? WHERE coordinator_id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssss", $name, $position, $email, $phone, $id);

    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // User info
            $user_id = $_SESSION['user_id'] ?? 'user_unknown';
            $fullname = $_SESSION['username'] ?? 'unknown user';

            // Transaction log
            $desc = "Updated coordinator profile (ID: $id)";
            logTransaction($pdo, $user_id, $fullname, $desc, "System");

            // Build only changed fields
            $oldChanged = [];
            $newChanged = [];

            if (($oldData['name'] ?? '') !== $name) {
    $oldChanged['name'] = $oldData['name'];
    $newChanged['name'] = $name;
}

            if (($oldData['position'] ?? '') !== $position) {
                $oldChanged['position'] = $oldData['position'];
                $newChanged['position'] = $position;
            }
            if (($oldData['email'] ?? '') !== $email) {
                $oldChanged['email'] = $oldData['email'];
                $newChanged['email'] = $email;
            }
            if (($oldData['phone'] ?? '') !== $phone) {
                $oldChanged['phone'] = $oldData['phone'];
                $newChanged['phone'] = $phone;
            }
            if (!empty($_FILES['profile_picture']['name']) && ($oldData['profile_picture'] ?? '') !== $imagePath) {
                $oldChanged['profile_picture'] = $oldData['profile_picture'];
                $newChanged['profile_picture'] = $imagePath;
            }

            // Only log if there's a change
            if (!empty($oldChanged) || !empty($newChanged)) {
                $activity = "Update coordinator profile ID [$id]";
                logAudit(
                    $pdo,
                    $user_id,
                    $activity,
                    json_encode($newChanged, JSON_UNESCAPED_SLASHES),
                    json_encode($oldChanged, JSON_UNESCAPED_SLASHES),
                    $fullname,
                    'Y'
                );
            }

            header("Location: coordinator.php?success=1");
exit();
        } else {
            echo "No changes made (same data or invalid ID).";
        }
    } else {
        echo "Query error: " . $stmt->error;
    }
}
?>
