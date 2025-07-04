<?php
session_start();
header('Content-Type: application/json'); // <-- ADD THIS

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
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit();
}

$post_id = $_POST["post_id"];
$user_id = $_SESSION["user_id"];

// Make sure this user owns the post
$stmt = $conn->prepare("DELETE FROM blog_posts WHERE post_id = ? AND trainee_id = (SELECT trainee_id FROM trainee WHERE user_id = ?)");
$stmt->bind_param("is", $post_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Delete failed"]);
}
?>
