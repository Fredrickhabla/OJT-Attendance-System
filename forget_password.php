<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username);
        $stmt->fetch();

        $reset_code = mt_rand(100000, 999999);
        $created_at = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $update = $conn->prepare("UPDATE users SET code=?, created_at=? WHERE user_id=?");
        $update->bind_param("ssi", $reset_code, $created_at, $user_id);
        $update->execute();

        $_SESSION['reset_email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'YOUR_EMAIL@gmail.com';
            $mail->Password = 'YOUR_APP_PASSWORD';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('YOUR_EMAIL@gmail.com', 'AcerOJT');
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset Code';
            $mail->Body = "Hello $username,<br><br>Your reset code is:<br><br><b>$reset_code</b><br><br>This code expires in 15 minutes.";

            $mail->send();
            $message = "A reset code has been sent to your email.";
        } catch (Exception $e) {
            $message = "Mailer Error: {$mail->ErrorInfo}";
        }
        $stmt->close();
    } else {
        $message = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Forgot Password - Acer OJT</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}
body {
  background: #f2f2f2; /* Plain background color instead of image */
  width: 100%;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
.form-wrapper {
  background: rgba(255, 255, 255, 0.95);
  padding: 40px 30px;
  border-radius: 12px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.form-wrapper h2 {
  font-size: 26px;
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}
form {
  width: 100%;
}
.input-group {
  position: relative;
  margin-bottom: 20px;
}
.input-group i {
  position: absolute;
  top: 50%;
  left: 14px;
  transform: translateY(-50%);
  color: #999;
  font-size: 16px;
}
input[type="email"] {
  width: 100%;
  padding: 14px 14px 14px 42px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 16px;
}
button {
  width: 100%;
  padding: 14px;
  background: #00bf63;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
button:hover {
  background: #009f50;
}
.message {
  background: #e6f4ea;
  color: #006400;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 16px;
  text-align: center;
}
.error {
  background: #ffe5e5;
  color: #b00020;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 16px;
  text-align: center;
}
.back-link {
  display: block;
  text-align: center;
  margin-top: 16px;
  font-size: 14px;
  color: #00bf63;
  text-decoration: none;
}
.back-link:hover {
  text-decoration: underline;
}
</style>
</head>
<body>
<div class="form-wrapper">
  <h2><i class="fas fa-unlock-alt"></i> Forgot Your Password?</h2>
  <?php if (!empty($message)): ?>
    <div class="<?= strpos($message, 'sent') !== false ? 'message' : 'error' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>
  <form method="POST">
    <div class="input-group">
      <i class="fas fa-envelope"></i>
      <input type="email" name="email" placeholder="Email Address" required />
    </div>
    <button type="submit"><i class="fas fa-paper-plane"></i> Reset Password</button>
  </form>
  <a href="indexv2.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to sign in</a>
</div>
</body>
</html>
