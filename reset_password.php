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

        $message = "Password successfully updated. You can now <a href='indexv2.php'>login</a>.";
    } else {
        $message = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - Acer OJT</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: sans-serif;
    }

    body {
      background-color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 1280px;
      background-color: white;
      overflow: hidden;
      box-shadow: 0 30px 40px 5px rgba(0, 0, 0, 0.4);
      border-radius: 32px;
      display: flex;
    }

    .left {
      flex: 1;
      padding: 48px;
      background-color: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .left h2 {
      font-size: 60px;
      font-weight: 400;
      line-height: 0.8;
    }

    .left h1 {
      font-size: 74px;
      font-weight: 900;
      margin: 10px 0;
      line-height: 1.0;
      font-family: "Racing Sans One";
    }

    .left p {
      font-size: 22px;
      margin-top: 10px;
      text-align: center;
    }

    .right {
      flex: 1;
      padding: 48px;
      background-color: #00bf63;
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-radius: 32px;
      max-width: 500px;
    }

    .right h2 {
      font-size: 36px;
      font-weight: 600;
      text-align: center;
      margin-bottom: 24px;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
      max-width: 300px;
      margin: 0 auto;
    }

    label {
      font-size: 18px;
    }

    input[type="password"] {
      height: 48px;
      border: none;
      border-radius: 999px;
      padding: 0 16px;
      background-color: white;
      font-size: 16px;
      width: 100%;
    }

    .signin-button {
      margin-top: 10px;
      height: 56px;
      border-radius: 999px;
      background-color: transparent; 
      color: white;                  
      font-weight: bold;
      border: 2px solid white;     
      cursor: pointer;
      width: 300px;
      font-size: 18px;
    }

    .signin-button:hover {
      background-color: rgba(255, 255, 255, 0.1); 
    }

    .message {
      color: yellow;
      text-align: center;
      font-weight: bold;
      margin-bottom: 16px;
    }

    .success a {
      color: #fff;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="left">
    <h2>Acer</h2>
    <h1>OJT System</h1>
    <p>Set a new password for your account.</p>
  </div>

  <div class="right">
    <h2>Reset Password</h2>

    <?php if ($message): ?>
      <p class="message <?= strpos($message, 'successfully') !== false ? 'success' : '' ?>">
        <?= $message ?>
      </p>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label for="password">New Password:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" class="signin-button">Update Password</button>
      </form>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
