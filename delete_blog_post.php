<?php
session_start();
header('Content-Type: application/json');

require_once 'logger.php';

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

if (!isset($_POST["post_id"])) {
    echo json_encode(["success" => false, "message" => "No post ID"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection error"]);
    exit();
}

$user_id = $_SESSION["user_id"];
$post_id = $_POST["post_id"];
$sys_user = $_SESSION["username"] ?? 'system_user';

// Get trainee info
$stmt = $conn->prepare("SELECT trainee_id, first_name, surname FROM trainee WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$trainee = $result->fetch_assoc();

if (!$trainee) {
    echo json_encode(["success" => false, "message" => "Trainee not found"]);
    exit();
}

$trainee_id = $trainee["trainee_id"];
$full_name = $trainee["first_name"] . " " . $trainee["surname"];

// Get existing blog post info (for logging)
$stmt = $conn->prepare("SELECT title, content FROM blog_posts WHERE post_id = ? AND trainee_id = ?");
$stmt->bind_param("ii", $post_id, $trainee_id);
$stmt->execute();
$post_result = $stmt->get_result();
$old_post = $post_result->fetch_assoc();

if (!$old_post) {
    echo json_encode(["success" => false, "message" => "Post not found or access denied"]);
    exit();
}

$old_value = "Title: " . $old_post["title"] . "\nContent: " . $old_post["content"];

// Proceed with delete
$stmt = $conn->prepare("DELETE FROM blog_posts WHERE post_id = ? AND trainee_id = ?");
$stmt->bind_param("ii", $post_id, $trainee_id);

if ($stmt->execute()) {
    // Log transaction and audit
    logTransaction($conn, $user_id, $full_name, "Deleted blog post ID $post_id", $sys_user);
    logAudit($conn, $user_id, "Delete Blog", "", $old_value, $sys_user);

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Delete failed", "error" => $stmt->error]);
}

$conn->close();
?>
