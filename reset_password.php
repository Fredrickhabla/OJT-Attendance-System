<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_SESSION['verified_email'])) {
        $message = "You must verify the code first.";
    } else {
        $email = $_SESSION['verified_email'];
        $password = $_POST['password'];

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password_hashed=?, code=NULL, create_at=NULL WHERE email=?");
        $update->bind_param("ss", $hashed, $email);
        if ($update->execute()) {
            $message = "Your password has been reset successfully.";
            session_unset();
            session_destroy();
        } else {
            $message = "Error updating password.";
        }
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
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <form method="POST">
        <label>Reset Code:</label><br>
        <input type="text" name="code" required><br><br>

        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br><br>

        <label>Confirm New Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
