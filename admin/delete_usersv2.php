<?php
session_start();
include('../conn.php');

// Check if the admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /ojtform/indexv2.php");
    exit;
}

// Get the user_id from the query string
$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    header("Location: manage_usersv2.php");
    exit;
}

// Check if the user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

// If you store a profile picture or other file, delete it here
// (Remove this part if you don't have a file to delete)
/*
if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
    unlink($user['profile_picture']);
}
*/

// Delete the user
$delete = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
$delete->execute([$user_id]);

// Redirect back to the user management page
header("Location: manage_usersv2.php");
exit;
?>
