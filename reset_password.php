<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$token = $_GET['token'] ?? '';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];

    // Check if token is valid and get username
    $stmt = $conn->prepare("SELECT username FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($username);

    if ($stmt->fetch()) {
        $stmt->close();

        $new_pass = password_hash($password, PASSWORD_DEFAULT);

        // Update user's password
        $update = $conn->prepare("UPDATE users SET password_hashed = ? WHERE username = ?");
        $update->bind_param("ss", $new_pass, $username);
        $update->execute();

        // Delete the token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE username = ?");
        $delete->bind_param("s", $username);
        $delete->execute();

        $message = "Password successfully updated. You can now <a href='login.php'>login</a>.";
    } else {
        $message = "Invalid token.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>

    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label>New Password:</label><br>
            <input type="password" name="password" required><br><br>
            <button type="submit">Update Password</button>
        </form>
    <?php endif; ?>
</body>
</html>
