<?php
session_start();
header('Content-Type: application/json');

require_once 'logger.php';

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

if (empty($title) || empty($content)) {
    echo json_encode(["success" => false, "message" => "Missing title or content"]);
    exit();
}

// Get trainee and user info for logging
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
$sys_user = $_SESSION["username"] ?? 'system_user';

if (empty($post_id) || $post_id === "0") {
    // INSERT new post
    $stmt = $conn->prepare("INSERT INTO blog_posts (trainee_id, title, content, status, created_at, updated_at) VALUES (?, ?, ?, 'published', NOW(), NOW())");
    $stmt->bind_param("sss", $trainee_id, $title, $content);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;

        // Log insert
        logTransaction($conn, $user_id, $full_name, "Created blog post '$title'", $sys_user);
        logAudit($conn, $user_id, "Insert Blog", "Title: $title\nContent: $content", "", $sys_user);

        echo json_encode(["success" => true, "post_id" => $new_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed", "error" => $stmt->error]);
    }

} else {
    // UPDATE existing post
    $oldStmt = $conn->prepare("SELECT title, content FROM blog_posts WHERE post_id = ?");
    $oldStmt->bind_param("i", $post_id);
    $oldStmt->execute();
    $oldPost = $oldStmt->get_result()->fetch_assoc();

    $old_title = $oldPost["title"] ?? '';
    $old_content = $oldPost["content"] ?? '';

    // Only proceed if something changed
    if ($old_title === $title && $old_content === $content) {
        echo json_encode(["success" => true, "message" => "No changes made"]);
    } else {
        $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, status = 'published', updated_at = NOW() WHERE post_id = ?");
        $stmt->bind_param("ssi", $title, $content, $post_id);

        if ($stmt->execute()) {
            $old_value = "Title: $old_title\nContent: $old_content";
            $new_value = "Title: $title\nContent: $content";

            logTransaction($conn, $user_id, $full_name, "Updated blog post ID $post_id", $sys_user);
            logAudit($conn, $user_id, "Update Blog", $new_value, $old_value, $sys_user);

            echo json_encode(["success" => true, "post_id" => $post_id]);
        } else {
            echo json_encode(["success" => false, "message" => "Update failed", "error" => $stmt->error]);
        }
    }
}

$conn->close();
?>
