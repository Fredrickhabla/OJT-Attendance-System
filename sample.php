<?php
session_start();

// DB connection for user login
$conn = new mysqli("localhost", "root", "", "ojtform");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginType = $_POST['login_type'] ?? 'user'; // 'user' or 'admin'
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($loginType === 'admin') {
        // Admin login (hardcoded)
        if ($username === 'admin' && $password === 'admin2314') {
            $_SESSION['ValidAdmin'] = true;
            $_SESSION['user_name'] = 'Administrator';
            header('Location: dashboardv2.php');
            exit;
        } else {
            $error = 'Invalid admin username or password.';
        }
    } else {
        // User login (database check)
        $stmt = $conn->prepare("SELECT id, password, full_name FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $hashed_password, $full_name);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['full_name'] = $full_name;
                    header("Location: attendance_form.php");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $error = "Database error.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Acer OJT Sign In</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    /* Your new login page styles */
    * {
      margin: 0; padding: 0; box-sizing: border-box; font-family: sans-serif;
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
    .container {
      width: 100%;
      max-width: 1280px;
      background-color: white;
      overflow: hidden;
      box-shadow: 0 30px 40px 5px rgba(0, 0, 0, 0.4);
      border-radius: 32px;
      display: flex;
      flex-wrap: wrap;
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
    input[type="text"],
    input[type="password"] {
      height: 48px;
      border: none;
      border-radius: 999px;
      padding: 0 16px;
      background-color: white;
      font-size: 16px;
    }
    .remember {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 16px;
    }
    .checkbox {
      width: 16px;
      height: 16px;
      cursor: pointer;
      margin-left: 20px;
    }
    .forgot {
      text-align: center;
      font-size: 16px;
      color: black;
      text-decoration: none;
      line-height: 0.2;
      margin-bottom: 20px;
    }
    .forgot:hover {
      text-decoration: underline;
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
    h1.acerojt {
      padding: 0px;
      margin: 0px;
      font-family: "Racing Sans One";
      font-weight: bold;
    }
    .acerlogs {
      margin-top: 40px;
    }

    /* New style for login type toggle */
    .login-type {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
      gap: 20px;
    }
    .login-type label {
      cursor: pointer;
      font-weight: bold;
      font-size: 18px;
      color: white;
      user-select: none;
    }
    .login-type input[type="radio"] {
      display: none;
    }
    .login-type input[type="radio"]:checked + label {
      color: #003f1d;
      background: white;
      padding: 5px 15px;
      border-radius: 999px;
      transition: 0.3s;
    }

    /* Password toggle icon */
    .position-relative {
      position: relative;
    }
    .position-relative i {
      position: absolute;
      top: 12px;
      right: 16px;
      cursor: pointer;
      color: #555;
      font-size: 20px;
    }

    /* Error message */
    .error-msg {
      background: #fff3f3;
      color: #b00020;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 10px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">

    <div class="left">
      <h2>Welcome to</h2>
      <h1 class="acerojt">AcerOJT</h1>
      <p class="pts">Proud to serve, proud of Acer</p>
      <img src="images/ojtlogo.png" class="acerlogs" alt="Acer Logo" style="width: 240px; margin-bottom: 20px; border-radius: 10px;" />
      <button class="signup-button transition" data-href="signup.php">Sign up</button>
    </div>

    <div class="right">
      <h2>Sign In</h2>

      <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="" id="login-form">
        <div class="login-type">
          <input type="radio" id="login-user" name="login_type" value="user" checked>
          <label for="login-user">User Login</label>

          <input type="radio" id="login-admin" name="login_type" value="admin">
          <label for="login-admin">Admin Login</label>
        </div>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required />

        <label for="password">Password</label>
        <div class="position-relative">
          <input type="password" id="password" name="password" required />
          <i class="bi bi-eye-slash" id="togglePassword"></i>
        </div>

        <div class="remember">
          <input class="checkbox" type="checkbox" id="remember" />
          <label for="remember">Remember me for 30 days</label>
        </div>

        <a href="#" class="forgot">Forgot Password?</a>

        <button type="submit" class="signin-button">Sign in</button>
      </form>
    </div>
  </div>

<script>
  // Page transition button
  document.querySelectorAll('button[data-href]').forEach(button => {
    button.addEventListener('click', () => {
      window.location.href = button.getAttribute('data-href');
    });
  });

  // Password toggle
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');

  togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    togglePassword.classList.toggle('bi-eye');
    togglePassword.classList.toggle('bi-eye-slash');
  });
</script>

</body>
</html>
