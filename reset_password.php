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
    .input-box {
    position: relative;
    width: 100%;
    max-width: 300px;
    margin-bottom: 20px;
    }

    .input-box input[type="password"],
    .input-box input[type="text"] {
    width: 100%;
    height: 48px;
    padding: 0 44px 0 16px;
    border: none;
    border-radius: 999px;
    font-size: 16px;
    background-color: white;
    box-shadow: 0 0 0 1px #ccc;
    outline: none;
    }

    .eye-toggle {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    cursor: pointer;
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

        <div class="input-box">
            <input type="password" name="password" id="password" required>
            <span class="eye-toggle" onclick="togglePassword()">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="gray" class="bi bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 
                1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 
                5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 
                1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 
                13 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 
                2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 
                7 0 3.5 3.5 0 0 1-7 0"/>
            </svg>
            </span>
        </div>

        <button type="submit" class="signin-button">Update Password</button>
        </form>
    <?php endif; ?>
  </div>
</div>
<script>
function togglePassword() {
  const input = document.getElementById('password');
  const icon = document.getElementById('eye-icon');

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}
</script>
</body>
</html>
