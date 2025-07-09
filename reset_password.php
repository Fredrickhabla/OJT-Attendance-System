<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$redirect = false;

if (!isset($_GET["username"])) {
    die("Username required.");
}

$username = $_GET["username"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hashed = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed, $username);
        $stmt->execute();
        $message = "Password has been reset successfully.";
        $redirect = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - Acer OJT</title>
  <?php if ($redirect): ?>
    <meta http-equiv="refresh" content="3;url=index.php">
  <?php endif; ?>
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
      box-shadow: 0 30px 40px 5px rgba(0, 0, 0, 0.4);
      border-radius: 32px;
      display: flex;
      overflow: hidden;
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
    }
    .left p {
      font-size: 22px;
      margin-top: 10px;
    }
    p.pts {
      margin: 8px;
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
    .message {
      background: #fff3f3;
      color: #b00020;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 10px;
      text-align: center;
    }
    .success {
      background: #e6f4ea;
      color: #006400;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 10px;
      text-align: center;
    }
    .button-pair {
      display: flex;
      gap: 16px;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .submit-button,
    .back-button {
      height: 56px;
      border-radius: 999px;
      background-color: transparent;
      color: white;
      font-weight: bold;
      border: 2px solid white;
      cursor: pointer;
      width: 140px;
      font-size: 16px;
      text-align: center;
      text-decoration: none;
      line-height: 56px;
    }
    .submit-button:hover,
    .back-button:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    h1.acerojt {
      font-weight: bold;
    }
    .acerlogs {
      margin-top: 40px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left">
      <h2>Reset Password</h2>
      <h1 class="acerojt">AcerOJT</h1>
      <p class="pts">Proud to serve, proud of Acer</p>
      <img src="images/ojtlogo.png" class="acerlogs" alt="Acer Logo" style="width: 240px; margin-bottom: 20px; border-radius: 10px;" />
      <a href="index.php" class="back-button">Return to Sign In</a>
    </div>
    <div class="right">
      <h2>Reset for <?= htmlspecialchars($username) ?></h2>
      <?php if ($message): ?>
        <div class="<?= strpos($message, 'successfully') !== false ? 'success' : 'message' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <form method="POST">
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required />

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required />

        <div class="button-pair">
          <button type="submit" class="submit-button">Reset</button>
          <a href="indexV2.php" class="back-button">Back</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
