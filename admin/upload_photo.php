<?php
session_start();
include('connection.php');

// Check admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get trainee ID
    $traineeId = $_POST['trainee_id'] ?? '';

    // Check if a file was uploaded successfully
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];

        if (in_array($fileExt, $allowed)) {
            // Build the filename WITHOUT extra "trainee_"
            // For example: uploads/trainee_686788dbce4b4.jfif
            $newFileName = "../uploads/" . $traineeId . "." . $fileExt;

            // Move the file to uploads folder
            if (move_uploaded_file($fileTmp, $newFileName)) {
                // Update the profile_picture column
                $stmt = $pdo->prepare("UPDATE trainee SET profile_picture = ? WHERE trainee_id = ?");
                $stmt->execute([$newFileName, $traineeId]);
            }
        }
    }

    // Redirect to traineeview.php with ?id=
    header("Location: traineeview.php?id=" . urlencode($traineeId));
    exit;
}
?>
