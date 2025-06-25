<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}

// Fetch users without 'created_at'
$stmt = $pdo->query("SELECT id, full_name, username FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - OJT Attendance Monitoring</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: url('../images/cover.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      padding-top: 70px;
    }
    .navbar-glass {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0,0,0,.1);
    }
    .content-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="dashboard.php">
      <i class="bi bi-people-fill"></i> OJT Attendance Monitoring
    </a>
    <div class="ms-auto">
      <a href="view_attendance.php" class="btn btn-outline-success btn-sm me-2">
        <i class="bi bi-calendar-week"></i> Attendance
      </a>
      <a href="manage_users.php" class="btn btn-outline-primary btn-sm me-2">
        <i class="bi bi-people-fill"></i> Manage Users
      </a>
      <a href="logout.php" class="btn btn-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="content-box">
    <h3 class="text-center text-success mb-4"><i class="bi bi-person-lines-fill"></i> User Accounts</h3>

    <?php if ($users): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Username</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="text-center">No users found.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
