<?php
session_start();
include('connection.php'); // Optional, in case you need DB for other features

if (isset($_SESSION['user_id']) || isset($_SESSION['ValidAdmin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Hardcoded login check
    if ($username === 'admin' && $password === 'admin2314') {
        $_SESSION['ValidAdmin'] = true;
        $_SESSION['user_name'] = 'Administrator';
        header('Location: dashboardv2.php');
        exit;
    } else {
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Login - OJT Attendance Monitoring System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: url('../images/cover.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
    }
    .navbar-glass {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    .navbar-brand {
      text-decoration: none !important;
    }
    .login-container {
      max-width: 400px;
      margin: 120px auto;
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 100, 0, 0.2);
      position: relative;
    }
    h2, label {
      color: #2e7d32;
    }
    .btn-success {
      background: #2e7d32;
      border-color: #2e7d32;
    }
    .alert {
      margin-top: 10px;
    }
    a {
      color: #2e7d32;
    }
    a:hover {
      text-decoration: underline;
      color: #1b5e20;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <i class="bi bi-calendar-check"></i> OJT Attendance Monitoring System
    </a>
  </div>
</nav>

<div class="login-container">
  <h2 align="center">Admin Login</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success w-100">Login</button>
  </form>
</div>

</body>
</html>
