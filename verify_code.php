<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=? AND code=? AND expires_at > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $_SESSION['verified_email'] = $email;
        $message = "Code verified. You can now reset your password.";
        // Optionally redirect:
        // header("Location: reset_password.php");
        // exit();
    } else {
        $message = "Invalid or expired code.";
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Verify Reset Code</title>
</head>
<body>
<h2>Verify Code</h2>
<?php if ($message): ?>
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
<form method="POST">
    <label>Email:</label>
    <input type="email" name="email" required>
    <label>Reset Code:</label>
    <input type="text" name="code" required>
    <button type="submit">Verify</button>
</form>
</body>
</html>
