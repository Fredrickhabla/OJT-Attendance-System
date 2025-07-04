<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection error"]);
    exit();
}

$user_id = $_SESSION["user_id"];
$post_id = $_POST["post_id"] ?? "";
$title = $_POST["title"] ?? "";
$content = $_POST["content"] ?? "";

// Validate input
if (empty($title) || empty($content)) {
    echo json_encode(["success" => false, "message" => "Missing title or content"]);
    exit();
}

// Get trainee_id using user_id
$stmt = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$trainee = $result->fetch_assoc();

if (!$trainee) {
    echo json_encode(["success" => false, "message" => "Trainee not found"]);
    exit();
}

$trainee_id = $trainee["trainee_id"];

if (empty($post_id) || $post_id === "0") {
    // INSERT new post using trainee_id
    $stmt = $conn->prepare("INSERT INTO blog_posts (trainee_id, title, content, status, created_at, updated_at) VALUES (?, ?, ?, 'published', NOW(), NOW())");
    $stmt->bind_param("sss", $trainee_id, $title, $content);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "post_id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed", "error" => $stmt->error]);
    }

} else {
    // UPDATE existing post
    $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, status = 'published', updated_at = NOW() WHERE post_id = ?");
    $stmt->bind_param("ssi", $title, $content, $post_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "post_id" => $post_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed", "error" => $stmt->error]);
    }
}

$conn->close();
?>
