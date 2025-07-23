<?php
session_start();
include('../conn.php');

if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No user ID provided.");
}

$user_id = intval($_GET['id']);

// Delete user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$user_id])) {
    header("Location: manage_users.php?deleted=success");
    exit;
} else {
    die("Failed to delete user.");
}
?>
