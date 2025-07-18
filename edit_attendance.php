<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
require_once 'connection.php';

$user_id = $_SESSION['user_id'];
$success = "";
$error   = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = $_POST['id'] ?? '';
    $date          = $_POST['date'] ?? '';
    $time_in    = $_POST['time_in'] ?? '';
    $time_out   = $_POST['time_out'] ?? '';
    $hours         = $_POST['hours'] ?? '';
    $description   = $_POST['work_description'] ?? '';
    $signature_path = $_POST['existing_signature'] ?? '';

    // Handle file upload
    if (!empty($_FILES['signature']['name']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = basename($_FILES['signature']['name']);
        $target_path = $upload_dir . time() . "_" . $filename;
        if (move_uploaded_file($_FILES['signature']['tmp_name'], $target_path)) {
            $signature_path = $target_path;
        }
    }

    $stmt = $conn->prepare("UPDATE attendance_records
        SET date = ?, morning_in = ?, morning_out = ?,
            hours = ?, work_description = ?, signature = ?
        WHERE id = ? AND user_id = ?");

    $stmt->bind_param("ssssssssii", $date, $morning_in, $morning_out,
                      $hours, $description, $signature_path, $id, $user_id);

    if ($stmt->execute()) {
        $success = "✅ Attendance updated successfully!";
    } else {
        $error = "❌ Failed to update attendance.";
    }
    $stmt->close();
}

// Fetch latest record
$stmt = $conn->prepare("SELECT * FROM attendance_records WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Attendance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('images/cover.jpg') no-repeat center/cover fixed;
      font-family: Arial, sans-serif;
      padding-top: 70px;
    }
    .navbar-glass {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    .alert-success-custom {
      max-width: 600px;
      margin: 20px auto;
      padding: 15px 20px;
      font-size: 17px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="#">OJT ATTENDANCE MONITOR SYSTEM</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navLinks">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-success fw-semibold" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Container -->
<div class="container py-5">
  <div class="card mb-4 shadow-sm border-success">
    <div class="card-body bg-white text-center">
      <h2 class="text-success"><i class="bi bi-pencil-square"></i> Edit Attendance Record</h2>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-success-custom text-center"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger text-center"><?= $error ?></div>
  <?php endif; ?>

  <?php if ($record): ?>
    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">
      <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">
      <input type="hidden" name="existing_signature" value="<?= htmlspecialchars($record['signature']) ?>">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($record['date']) ?>" required>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Time In</label>
          <input type="time" name="time_in" class="form-control" value="<?= substr($record['time_in'], 0, 5) ?>" required>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Time Out</label>
          <input type="time" name="time_out" class="form-control" value="<?= substr($record['time_out'], 0, 5) ?>" required>
        </div>
        
        <div class="col-md-3 mb-3">
          <label class="form-label">Hours</label>
          <input type="number" step="0.1" name="hours" class="form-control" value="<?= htmlspecialchars($record['hours']) ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Work Description</label>
        <textarea name="work_description" class="form-control" required><?= htmlspecialchars($record['work_description']) ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">E-Signature (optional)</label><br>
        <?php if (!empty($record['signature']) && file_exists($record['signature'])): ?>
          <img src="<?= htmlspecialchars($record['signature']) ?>" width="150" class="mb-2 border"><br>
        <?php endif; ?>
        <input type="file" name="signature" class="form-control">
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-success me-2">
          <i class="bi bi-save2-fill"></i> Update Attendance
        </button>
        <a href="success.php" class="btn btn-secondary">
          <i class="bi bi-arrow-return-left"></i> Back
        </a>
      </div>
    </form>
  <?php else: ?>
    <div class="alert alert-warning text-center">No attendance record found.</div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
