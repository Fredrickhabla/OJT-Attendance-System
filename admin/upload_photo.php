<?php
session_start();
include('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $traineeId = $_POST['trainee_id'] ?? '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = basename($_FILES['photo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];

        if (in_array($fileExt, $allowed)) {
            // Remove the extra trainee_ prefix here
            $newFileName = "uploads/" . $traineeId . "." . $fileExt;

            if (move_uploaded_file($fileTmp, $newFileName)) {
                $stmt = $pdo->prepare("UPDATE trainee SET profile_picture = ? WHERE trainee_id = ?");
                $stmt->execute([$newFileName, $traineeId]);
            }
        }
    }

    header("Location: traineeview.php?id=" . urlencode($traineeId));
    exit;
}
?>
