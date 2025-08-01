<?php

include('../conn.php');

$username = 'admin';
$password = 'admin2314';
$fullName = 'Administrator';

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() > 0) {
    echo "Admin user already exists.";
} else {
    $insert = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
    $insert->execute([$username, $hashedPassword, $fullName]);
    echo "Admin user successfully created.";
}
?>
