<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "You are not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];
$current = $_POST['current'] ?? '';
$new = $_POST['new'] ?? '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        echo "Current password is incorrect.";
        exit;
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$newHash, $user_id]);

    echo "Password updated successfully.";
} catch (PDOException $e) {
    echo "Database error.";
}
