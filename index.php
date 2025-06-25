<?php
session_start();

// Connect to MySQL database
$conn = new mysqli("localhost", "root", "", "ojtform");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - OJT Attendance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('images/cover.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
    }
    .navbar-glass {
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .login-container {
      max-width: 400px;
      margin: 120px auto;
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 100, 0, 0.2);
    }
    h2 { color: #2e7d32; text-align: center; margin-bottom: 20px; }
    label { color: #2e7d32; }
    .btn-success { background-color: #2e7d32; border-color: #2e7d32; }
    .alert { margin-top: 10px; }
    .logo-img { display: block; margin: 0 auto 20px; max-width: 120px; border-radius: 10px; }
    a { color: #2e7d32; text-decoration: none; }
    a:hover { text-decoration: underline; color: #1b5e20; }
  </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="#">OJT ATTENDANCE SYSTEM</a>
  </div>
</nav>

<!-- Login Form -->
<div class="login-container">
  <img src="images/logo.jpg" alt="OJT Logo" class="logo-img">
  <h2>Login</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="username" class="form-label">Username:</label>
      <input type="text" id="username" name="username" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password:</label>
      <div class="input-group">
        <input type="password" id="password" name="password" class="form-control" required>
        <span class="input-group-text bg-white">
          <i class="bi bi-eye-slash" id="togglePassword" style="cursor:pointer;"></i>
        </span>
      </div>
    </div>

    <button type="submit" class="btn btn-success w-100">Login</button>

    <p class="mt-3 text-center">
      Don't have an account? <a href="register.php">Register here</a>
    </p>
  </form>
</div>

<!-- Bootstrap JS + Password Toggle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggle = document.getElementById('togglePassword');
  const pwd = document.getElementById('password');

  toggle.addEventListener('click', () => {
    const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
    pwd.setAttribute('type', type);
    toggle.classList.toggle('bi-eye');
    toggle.classList.toggle('bi-eye-slash');
  });
</script>
</body>
</html>
