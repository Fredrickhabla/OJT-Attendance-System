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
        $token = bin2hex(random_bytes(50));

        $conn->query("DELETE FROM password_resets WHERE username = '$username'");

        $insert = $conn->prepare("INSERT INTO password_resets (username, token) VALUES (?, ?)");
        $insert->bind_param("ss", $username, $token);
        $insert->execute();

        header("Location: reset_password.php?token=$token");
        exit;
    } else {
        $message = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password - Acer OJT</title>
  <link rel="stylesheet" href="styles.css" />
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
      max-width: 1060px;
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

    input[type="text"] {
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

    .error-message {
      color: yellow;
      text-align: center;
      font-weight: bold;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="left">
    <h2>Acer</h2>
    <h1>OJT System</h1>
    <p>Enter your username to reset your password.</p>
  </div>

  <div class="right">
    <h2>Forgot Password</h2>

    <?php if ($message): ?>
      <p class="error-message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
      <label for="username">Username:</label>
      <input type="text" name="username" id="username" required>

      <button type="submit" class="signin-button">Reset Password</button>
    </form>
  </div>
</div>

</body>
</html>
