<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "Unauthorized access.";
    exit();
}

$user_id = $_SESSION["user_id"];
$current = $_POST['current'] ?? '';
$new = $_POST['new'] ?? '';

if (empty($current) || empty($new)) {
    echo "All fields are required.";
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get current password hash
    $stmt = $pdo->prepare("SELECT password_hashed FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password_hashed'])) {
        echo "Current password is incorrect.";
        exit();
    }

    // Update to new password
    $newHashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hashed = ? WHERE user_id = ?");
    $stmt->execute([$newHashed, $user_id]);

    echo "Password changed successfully.";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage(); // for debugging
}
?>
