<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$trainee = $result->fetch_assoc();

if (!$trainee) {
    echo json_encode([]);
    exit();
}

$trainee_id = $trainee['trainee_id'];
$search = isset($_GET['query']) ? trim($_GET['query']) : '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT post_id, title, content, created_at, updated_at FROM blog_posts WHERE trainee_id = ? AND title LIKE CONCAT('%', ?, '%') ORDER BY created_at DESC");
    $stmt->bind_param("ss", $trainee_id, $search);
} else {
    $stmt = $conn->prepare("SELECT post_id, title, content, created_at, updated_at FROM blog_posts WHERE trainee_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $trainee_id);
}

$stmt->execute();
$result = $stmt->get_result();
$posts = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

header('Content-Type: application/json');
echo json_encode($posts);
