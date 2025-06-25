<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: view_attendance.php");
    exit;
}

// Fetch the current record
$stmt = $pdo->prepare("SELECT * FROM attendance_records WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    echo "Attendance record not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $morning_in = $_POST['morning_in'];
    $morning_out = $_POST['morning_out'];
    $afternoon_in = $_POST['afternoon_in'];
    $afternoon_out = $_POST['afternoon_out'];
    $hours = $_POST['hours'];
    $work_description = $_POST['work_description'];

    $update = $pdo->prepare("UPDATE attendance_records SET 
        date = ?, morning_in = ?, morning_out = ?, afternoon_in = ?, afternoon_out = ?, hours = ?, work_description = ?
        WHERE id = ?");
    $update->execute([$date, $morning_in, $morning_out, $afternoon_in, $afternoon_out, $hours, $work_description, $id]);

    header("Location: view_attendance.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Attendance - OJT Attendance System</title>
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
    .edit-box {
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
      color: #2e7d32;
    }
    .btn-success {
      background-color: #2e7d32;
      border-color: #2e7d32;
    }
    .btn-secondary {
      background-color: #ccc;
      border-color: #aaa;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="dashboard.php">
      <i class="bi bi-calendar-check"></i> OJT Attendance System
    </a>
    <div class="ms-auto">
      <a href="view_attendance.php" class="btn btn-secondary btn-sm me-2">Back</a>
      <a href="logout.php" class="btn btn-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<!-- Form Container -->
<div class="container mt-5">
  <div class="edit-box">
    <h3 class="text-center mb-4"><i class="bi bi-pencil-square"></i> Edit Attendance Record</h3>

    <form method="POST">
      <div class="mb-3">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($record['date']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Morning In</label>
        <input type="time" name="morning_in" class="form-control" value="<?= htmlspecialchars($record['morning_in']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Morning Out</label>
        <input type="time" name="morning_out" class="form-control" value="<?= htmlspecialchars($record['morning_out']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Afternoon In</label>
        <input type="time" name="afternoon_in" class="form-control" value="<?= htmlspecialchars($record['afternoon_in']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Afternoon Out</label>
        <input type="time" name="afternoon_out" class="form-control" value="<?= htmlspecialchars($record['afternoon_out']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Total Hours</label>
        <input type="number" step="0.1" name="hours" class="form-control" value="<?= htmlspecialchars($record['hours']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Work Description</label>
        <textarea name="work_description" class="form-control" rows="4" required><?= htmlspecialchars($record['work_description']) ?></textarea>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Update Record</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
