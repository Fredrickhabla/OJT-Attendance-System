<?php
session_start();
include('connection.php');

// Check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $traineeId = $_POST['trainee_id'] ?? '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmpFile = $_FILES['photo']['tmp_name'];
        $originalName = basename($_FILES['photo']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];

        if (in_array($ext, $allowed)) {
            // Make sure the upload directory exists
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ojtform/uploads/';
            $webPathBase = 'uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Always save as uploads/{traineeId}.{ext}
            $filename = $traineeId . "." . $ext;
            $fullPath = $uploadDir . $filename;
            $webPath = $webPathBase . $filename;

            if (move_uploaded_file($tmpFile, $fullPath)) {
                // Save relative path in database
                $stmt = $pdo->prepare("UPDATE trainee SET profile_picture = ? WHERE trainee_id = ?");
                $stmt->execute([$webPath, $traineeId]);
            }
        }
    }

    header("Location: traineeview.php?id=" . urlencode($traineeId));
    exit;
}
?>
