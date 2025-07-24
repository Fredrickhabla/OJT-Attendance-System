<?php
session_start();
include('../conn.php');
include('../logger.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /ojtform/indexv2.php");
    exit;
}

$sys_user = $_SESSION['user_id'] ?? 'unknown';
$sys_name = $_SESSION['username'] ?? 'Unknown Admin';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    header("Location: manage_usersv2.php");
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

$old_status = $user['active'];
$fullname = $user['name'] ?? 'N/A';


$archive = $pdo->prepare("UPDATE users SET active = 'N' WHERE user_id = ?");
$success = $archive->execute([$user_id]);


logTransaction(
    $pdo,
    $sys_user,
    $sys_name,
    "User archived: $user_id.",
    $sys_name
);


logAudit(
    $pdo,
    $sys_user,
    "Archive User: $user_id",
    "active = 'N'",
    "active = '$old_status'",
    $sys_name,
    $success ? 'Y' : 'N'
);

header("Location: manage_usersv2.php");
exit;
?>
