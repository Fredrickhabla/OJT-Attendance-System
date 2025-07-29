<?php
require_once 'connection.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing coordinator ID"]);
    exit();
}

$coordinator_id = $_GET['id'];

$stmt = $conn->prepare("SELECT name, position, email, phone, profile_picture FROM coordinator WHERE coordinator_id = ?");
$stmt->bind_param("s", $coordinator_id);
$stmt->execute();
$stmt->bind_result($name, $position, $email, $phone, $profile_picture);

if ($stmt->fetch()) {
    echo json_encode([
        "name" => $name,
        "position" => $position,
        "email" => $email,
        "phone" => $phone,
        "profile_picture" => $profile_picture
    ]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Coordinator not found"]);
}

$stmt->close();
