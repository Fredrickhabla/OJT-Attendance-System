<?php
session_start();
include('../conn.php');

if (!isset($_SESSION['ValidAdmin'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: view_attendance.php");
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM attendance_records WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    echo "Attendance record not found.";
    exit;
}

// Delete on POST confirm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete = $pdo->prepare("DELETE FROM attendance_records WHERE id = ?");
    $delete->execute([$id]);
    header("Location: view_attendance.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Attendance - OJT Attendance System</title>
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
    .box {
      max-width: 600px;
      margin: auto;
      background: rgba(255,255,255,0.95);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    .navbar-glass {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    h3 {
      color: #d32f2f;
    }
    .btn-danger {
      background-color: #d32f2f;
      border-color: #d32f2f;
    }
    .btn-secondary {
      background-color: #ccc;
      border-color: #aaa;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="dashboard.php">
      <i class="bi bi-calendar-check"></i> OJT Attendance System
    </a>
    <div class="ms-auto">
      <a href="logout.php" class="btn btn-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="box text-center">
    <h3><i class="bi bi-exclamation-triangle"></i> Confirm Delete</h3>
    <p class="mb-4">Are you sure you want to delete this attendance record for <strong><?= htmlspecialchars($record['date']) ?></strong>?</p>

    <form method="POST">
      <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Yes, Delete</button>
      <a href="view_attendance.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>

</body>
</html>
