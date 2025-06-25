<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header('Location: index.php');
    exit;
}

$adminName = $_SESSION['user_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - OJT Attendance Monitoring System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: url('../images/cover.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      padding-top: 60px;
    }
    .navbar-glass {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(6px);
    }
    .dashboard-box {
      background: white;
      padding: 40px;
      max-width: 600px;
      margin: 0 auto;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <i class="bi bi-speedometer2"></i> OJT Attendance Monitoring System
    </a>
    <div class="ms-auto">
      <a href="logout.php" class="btn btn-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="dashboard-box mt-5">
  <h2>Welcome, <?= htmlspecialchars($adminName) ?>!</h2>
  <p class="mt-3">You are now in the admin dashboard.</p>

  <a href="view_attendance.php" class="btn btn-outline-success mt-3">
    <i class="bi bi-calendar-check-fill"></i> View Attendance Records
  </a>

  <a href="manage_users.php" class="btn btn-outline-primary mt-3">
    <i class="bi bi-people-fill"></i> Manage Users
  </a>
</div>

</body>
</html>




