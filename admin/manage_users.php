<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $pdo->query("SELECT id, full_name, username FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users.csv"');
    $output = fopen("php://output", "w");

    fputcsv($output, ['ID', 'Full Name', 'Username']);
    foreach ($users as $user) {
        fputcsv($output, [$user['id'], $user['full_name'], $user['username']]);
    }
    fclose($output);
    exit;
}

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
      font-size: 0.95rem;
    }
    .navbar-glass {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0,0,0,.1);
    }
    .content-box {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
      overflow-x: auto;
      max-width: 750px;
      margin: 0 auto;
    }
    table {
      font-size: 0.9rem;
    }
    table td, table th {
      padding: 8px 12px;
      word-break: break-word;
      max-width: 250px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="dashboard.php">
      <i class="bi bi-people-fill"></i> OJT Attendance System
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

<div class="container mt-4">
  <div class="content-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="text-success mb-0"><i class="bi bi-person-lines-fill"></i> User Accounts</h4>
      <a href="?export=csv" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-download"></i> Export CSV
      </a>
    </div>

    <?php if ($users): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-sm">
        <thead class="table-success text-center">
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td class="text-center">
              <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary me-1">
                <i class="bi bi-pencil-fill"></i>
              </a>
              <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Are you sure you want to delete this user?');">
                <i class="bi bi-trash-fill"></i>
              </a>
            </td>
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
