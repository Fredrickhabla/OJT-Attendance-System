<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);

    // Check if username exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // Generate token
        $token = bin2hex(random_bytes(50));

        // Optional: delete previous tokens
        $conn->query("DELETE FROM password_resets WHERE username = '$username'");

        // Save token
        $insert = $conn->prepare("INSERT INTO password_resets (username, token) VALUES (?, ?)");
        $insert->bind_param("ss", $username, $token);
        $insert->execute();

        // Redirect to reset form with token
        header("Location: reset_password.php?token=$token");
        exit;
    } else {
        $message = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password (Username Only)</h2>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
