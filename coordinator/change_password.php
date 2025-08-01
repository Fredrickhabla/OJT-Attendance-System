<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    $_SESSION['change_error'] = "Unauthorized access.";
    header("Location: coordupdate.php");
    exit();
}

require_once '../logger.php';

$user_id = $_SESSION["user_id"];
$current = $_POST['current'] ?? '';
$new = $_POST['new'] ?? '';

if (empty($current) || empty($new)) {
    $_SESSION['change_error'] = "All fields are required.";
    header("Location: coordupdate.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $stmt = $pdo->prepare("SELECT username, name, password_hashed FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password_hashed'])) {
        $_SESSION['change_error'] = "Current password is incorrect.";
        header("Location: coordupdate.php");
        exit();
    }

    
    $newHashed = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hashed = ? WHERE user_id = ?");
    $stmt->execute([$newHashed, $user_id]);

   
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

    $_SESSION['change_success'] = "Password changed successfully.";
    header("Location: coordupdate.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['change_error'] = "Database error: " . $e->getMessage(); 
    header("Location: coordupdate.php");
    exit();
}
