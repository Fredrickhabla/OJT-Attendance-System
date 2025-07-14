<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    echo "Unauthorized access.";
    exit();
}

require_once 'logger.php';

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

    // Get current user for verification + logging
    $stmt = $pdo->prepare("SELECT  username, name, password_hashed FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password_hashed'])) {
        echo "Current password is incorrect.";
        exit();
    }

    // Change password
    $newHashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hashed = ? WHERE user_id = ?");
    $stmt->execute([$newHashed, $user_id]);

    // Logging
    $sys_user = $user['username'] ?? 'unknown_user';

    $oldInputValues = [
        'password_hashed' => $user['password_hashed'] ? 'hashed_password_exists' : 'none'
    ];

    $newInputValues = [
        'current_password' => '******',
        'new_password' => '******'
    ];

    logTransaction($pdo, $user_id, $user['name'], "Changed Password", $sys_user);
    logAudit($pdo, $user_id, "Change Password", json_encode($newInputValues), json_encode($oldInputValues), $sys_user);

    echo "Password changed successfully.";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage(); 
}
?>
