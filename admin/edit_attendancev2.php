<?php
session_start();
include('../conn.php');
require_once 'logger.php';

$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) &&
   (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /ojtform/indexv2.php?timeout=1"); 
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

// Get and validate attendance ID from URL
$attendance_id = $_GET['attendance_id'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $attendance_id)) {
    die("Invalid attendance ID format.");
}

// Fetch record from the correct table
$stmt = $pdo->prepare("SELECT * FROM attendance_record WHERE attendance_id = ?");
$stmt->execute([$attendance_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("Record not found.");
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $time_in = $_POST['time_in'] ?? '';
    $time_out = $_POST['time_out'] ?? '';
    $hours = $_POST['hours'] ?? '';
    $work_description = $_POST['work_description'] ?? '';
    $signature_path = $record['signature']; // default to old

    // Handle signature upload
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ojtform/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['signature']['name']);
        $fullTargetPath = $uploadDir . $filename;
        $webPath = 'uploads/' . $filename;

        if (move_uploaded_file($_FILES['signature']['tmp_name'], $fullTargetPath)) {
            $signature_path = $webPath;
        } else {
            $error = "Failed to upload the signature. Error code: " . $_FILES['signature']['error'];
        }
    }

    // Proceed with update if valid
    if (!$error && $date && $time_in && $time_out && $hours) {
        // Save old values for audit
        $old_values_array = [
            'date' => $record['date'],
            'time_in' => $record['time_in'],
            'time_out' => $record['time_out'],
            'hours' => $record['hours'],
            'work_description' => $record['work_description'],
            'signature' => $record['signature']
        ];

        // Perform update
        $update = $pdo->prepare("UPDATE attendance_record SET date=?, time_in=?, time_out=?, hours=?, work_description=?, signature=? WHERE attendance_id=?");
        if ($update->execute([$date, $time_in, $time_out, $hours, $work_description, $signature_path, $attendance_id])) {
            $success = "Record updated successfully.";

            // Get admin info
            $admin_id = $_SESSION['user_id'] ?? 'unknown';
            $admin_name = $_SESSION['username'] ?? 'Unknown Admin';

            // Log transaction
            logTransaction($pdo, $admin_id, $admin_name, "Updated attendance record ID: $attendance_id", $admin_name);

            // New values for comparison
            $new_values_array = [
                'date' => $date,
                'time_in' => $time_in,
                'time_out' => $time_out,
                'hours' => $hours,
                'work_description' => $work_description,
                'signature' => $signature_path
            ];

            // Detect changes
            $changed_old = [];
            $changed_new = [];

            foreach ($old_values_array as $field => $old_val) {
                $new_val = $new_values_array[$field];
                if ($old_val != $new_val) {
                    $changed_old[$field] = $old_val;
                    $changed_new[$field] = $new_val;
                }
            }

            // Log changes
            if (!empty($changed_old)) {
                logAudit(
                    $pdo,
                    $admin_id,
                    "Edit Attendance Record: $attendance_id",
                    json_encode($changed_new),
                    json_encode($changed_old),
                    $admin_name,
                    'Y'
                );
            }

            // Refresh current record
            $stmt->execute([$attendance_id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update the record.";
        }
    } elseif (!$error) {
        $error = "Please fill in all required fields.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Attendance Record</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background-color: #f0f2f5;
      color: #333;
    }
    .layout {
      display: flex;
      height: 100vh;
    }
    .sidebar {
      width: 300px;
      background-color: #44830f;
      color: white;
      padding: 24px;
      display: flex;
      flex-direction: column;
    }
    .sidebar h1 {
      font-size: 22px;
      margin-bottom: 40px;
      text-align: center;
    }
    .menu-label {
      text-transform: uppercase;
      font-size: 13px;
      letter-spacing: 1px;
      margin-bottom: 16px;
      opacity: 0.8;
    }
    .nav {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .nav a {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      transition: 0.2s;
    }
    .nav a:hover {
      background-color: #14532d;
    }
    .logout {
      margin-top: auto;
    }
    .logout a {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: 0.2s;
    }
    .logout a:hover {
      background-color: #2c6b11;
    }
    .bi {
      margin-right: 6px;
    }
    .acerlogo {
      text-align: center;
      font-size: 20px;
    }
    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .topbar {
      background-color: #14532d;
      color: white;
      padding: 10px 16px;
      font-size: 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
      height: 55px;
      text-align: left;
    }
    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }
    .form-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
      max-width: 600px;
      margin: 0 auto;
    }
    .signature-img {
      max-width: 120px;
      max-height: 60px;
      display: block;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="layout">
<aside class="sidebar">
  <div>
    <h1 class="acerlogo">OJT - ACER</h1>
    <div class="menu-label">Menu</div>
    <nav class="nav">
      <a href="dashboardv2.php">
        <span style="display:inline-flex; align-items:center; margin-right:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 4l9 5.75V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z" />
          </svg>
        </span>
        Dashboard
      </a>
      <a href="trainee.php">
        <span style="display:inline-flex; align-items:center; margin-right:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </span>
        Trainee
      </a>
      <a href="coordinator.php">
        <span style="display:inline-flex; align-items:center; margin-right:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7m0-7l-9-5m9 5l9-5" />
          </svg>
        </span>
        Coordinator
      </a>
      <a href="report.php">
        <span style="display:inline-flex; align-items:center; margin-right:8px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
          </svg>
        </span>
        Report
      </a>
    </nav>
  </div>
  <div class="logout">
    <a href="logout.php">
      <i class="bi bi-box-arrow-right" style="margin-right:8px;"></i>
      Logout
    </a>
  </div>
</aside>


  <div class="content">
    <div class="topbar">Edit Attendance Record</div>
    <div class="main">
      <div class="form-container">
        <?php if ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($record['date']) ?>" required>
          </div>
          <div class="mb-3">
  <label class="form-label">Time In</label>
  <input type="time" id="time_in" name="time_in" class="form-control" value="<?= htmlspecialchars($record['time_in']) ?>" required>
</div>
<div class="mb-3">
  <label class="form-label">Time Out</label>
  <input type="time" id="time_out" name="time_out" class="form-control" value="<?= htmlspecialchars($record['time_out']) ?>" required>
</div>
<div class="mb-3">
  <label class="form-label">Hours</label>
  <input type="number" id="hours" name="hours" step="0.01" class="form-control" value="<?= htmlspecialchars($record['hours']) ?>" required readonly>
</div>

          <div class="mb-3">
            <label class="form-label">Work Description</label>
            <textarea name="work_description" class="form-control"><?= htmlspecialchars($record['work_description']) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Current Signature</label><br>
            <?php if (!empty($record['signature'])): ?>
              <a href="/ojtform/<?= htmlspecialchars($record['signature']) ?>" target="_blank">
                <img src="/ojtform/<?= htmlspecialchars($record['signature']) ?>" alt="Signature" class="signature-img">
              </a>
            <?php else: ?>
              <span class="text-muted">No signature</span>
            <?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Upload New Signature (optional)</label>
            <input type="file" name="signature" class="form-control">
          </div>
          <div style="text-align: center;">
          <button type="submit" class="btn btn-success">Update Record</button>
          <a href="view_attendancev2.php" class="btn btn-secondary">Back to Records</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function calculateHours() {
    const timeIn = document.getElementById("time_in").value;
    const timeOut = document.getElementById("time_out").value;
    const hoursField = document.getElementById("hours");

    if (timeIn && timeOut) {
      const [inHours, inMinutes] = timeIn.split(":").map(Number);
      const [outHours, outMinutes] = timeOut.split(":").map(Number);

      const start = new Date();
      const end = new Date();

      start.setHours(inHours, inMinutes, 0);
      end.setHours(outHours, outMinutes, 0);

      let diff = (end - start) / 1000 / 60 / 60; // in hours
      if (diff < 0) diff += 24; // handle overnight times

      hoursField.value = diff.toFixed(2);
    }
  }

  document.getElementById("time_in").addEventListener("change", calculateHours);
  document.getElementById("time_out").addEventListener("change", calculateHours);

  // Optional: auto-calculate on page load
  window.addEventListener("DOMContentLoaded", calculateHours);
</script>
<script src="/ojtform/autologout.js"></script>
</body>
</html>
