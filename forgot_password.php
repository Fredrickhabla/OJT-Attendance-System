<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);

    // Check if username exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        // Redirect to reset page with username in URL
        header("Location: reset_password.php?username=" . urlencode($username));
        exit();
    } else {
        $message = "Username not found.";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acer OJT - Forgot Password</title>
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
      opacity: 1;
      transition: opacity 0.6s ease;
    }
    body.fade-out {
      opacity: 0;
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
      font-family: "Canva Sans";
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
    }
    p.pts {
      margin: 8px;
    }
    .signup-button {
      margin-top: 30px;
      width: 280px;
      height: 56px;
      border: 2px solid black;
      border-radius: 999px;
      background-color: transparent;
      cursor: pointer;
      font-weight: bold;
      font-size: 18px;
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
    .message {
      background: #fff3f3;
      color: #b00020;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 10px;
      text-align: center;
    }
    .back-button {
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
      text-align: center;
      text-decoration: none;
      line-height: 56px;
    }
    .back-button:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .submit-button {
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
    .submit-button:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    h1.acerojt {
      padding: 0px;
      margin: 0px;
      font-family: "Racing Sans One";
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
      <h2>Forgot Password</h2>
      <h1 class="acerojt">AcerOJT</h1>
      <p class="pts">Proud to serve, proud of Acer</p>
      <img src="images/ojtlogo.png" class="acerlogs" alt="Acer Logo" style="width: 240px; margin-bottom: 20px; border-radius: 10px;" />
      <a href="index.php" class="back-button">Back to Sign In</a>
    </div>

    <div class="right">
      <h2>Reset your password</h2>
      <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="POST">
        <label for="username">Enter your username:</label>
        <input type="text" id="username" name="username" required />
        <button type="submit" class="submit-button">Next</button>
      </form>
    </div>
  </div>
</body>
</html>
