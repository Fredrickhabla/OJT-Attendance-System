<?php
session_start();
include('connection.php');
require_once 'logger.php'; // <-- Add this line


if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $traineeId = $_POST['trainee_id'] ?? '';

    
    $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmpFile = $_FILES['photo']['tmp_name'];
        $originalName = basename($_FILES['photo']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];

        if (in_array($ext, $allowed)) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ojtform/uploads/';
            $webPathBase = 'uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            
            $filename = $traineeId . "_" . time() . "." . $ext;
            $fullPath = $uploadDir . $filename;
            $webPath = $webPathBase . $filename;

            
            $oldStmt = $pdo->prepare("SELECT profile_picture FROM trainee WHERE trainee_id = ?");
            $oldStmt->execute([$traineeId]);
            $oldPhoto = $oldStmt->fetchColumn();

            if (move_uploaded_file($tmpFile, $fullPath)) {
                
                $stmt = $pdo->prepare("UPDATE trainee SET profile_picture = ? WHERE trainee_id = ?");
                $stmt->execute([$webPath, $traineeId]);

                
                $userId = $_SESSION['user_id'] ?? null;
                $fullname = $_SESSION['username'] ?? 'Unknown User';
                $transactionUser = $traineeId; 

                if ($userId) {
                    
                    logTransaction($pdo, $userId, $fullname, "Updated trainee profile photo", $fullname);
                    logAudit($pdo, $userId, "Updated trainee profile photo [$traineeId]", $webPath, $oldPhoto, $fullname);
                }
            }
        }
    }
    
    header("Location: traineeview.php?id=" . urlencode($traineeId) . "&photo=success");
exit;
}
?>
