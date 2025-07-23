<?php
// Use the same connection file
include('../conn.php');

// Credentials
$username = 'admin';
$password = 'admin2314';
$fullName = 'Administrator';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
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
