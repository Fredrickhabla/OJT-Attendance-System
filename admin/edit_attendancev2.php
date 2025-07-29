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


$attendance_id = $_GET['attendance_id'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $attendance_id)) {
    die("Invalid attendance ID format.");
}


$stmt = $pdo->prepare("
  SELECT ar.*, CONCAT(t.first_name, ' ', t.surname) AS full_name
  FROM attendance_record ar
  JOIN trainee t ON ar.trainee_id = t.trainee_id
  WHERE ar.attendance_id = ?
");
$stmt->execute([$attendance_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("Record not found.");
}


$trainee_id = $record['trainee_id'];
$scheduleStmt = $pdo->prepare("SELECT schedule_start FROM trainee WHERE trainee_id = ?");
$scheduleStmt->execute([$trainee_id]);
$traineeData = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
$schedule_start = $traineeData['schedule_start'] ?? '08:00'; 

$success = "";
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $time_in = $_POST['time_in'] ?? '';
    $time_out = $_POST['time_out'] ?? '';

    $trainee_id = $record['trainee_id'];
    $scheduleStmt = $pdo->prepare("SELECT schedule_start FROM trainee WHERE trainee_id = ?");
    $scheduleStmt->execute([$trainee_id]);
    $traineeData = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
    $schedule_start = $traineeData['schedule_start'] ?? '08:00'; 

    $hours = 0;
    $hours_late = 0;

    try {
        $timeInObj = new DateTime("$date $time_in");
        $timeOutObj = new DateTime("$date $time_out");

    
        if ($timeOutObj < $timeInObj) {
            $timeOutObj->modify('+1 day');
        }

        $workedInterval = $timeInObj->diff($timeOutObj);
        $hours = round($workedInterval->h + ($workedInterval->i / 60), 2);

        $scheduleObj = new DateTime("$date $schedule_start");
        if ($timeInObj > $scheduleObj) {
            $lateInterval = $scheduleObj->diff($timeInObj);
            $hours_late = round($lateInterval->h + ($lateInterval->i / 60), 2);
        }

    } catch (Exception $e) {
        $error = "Invalid date/time format.";
    }

    if (!$error && $date && $time_in && $time_out) {
        $old_values_array = [
            'date' => $record['date'],
            'time_in' => $record['time_in'],
            'time_out' => $record['time_out'],
            'hours' => $record['hours'],
            'hours_late' => $record['hours_late']
        ];

        $update = $pdo->prepare("UPDATE attendance_record SET date=?, time_in=?, time_out=?, hours=?, hours_late=? WHERE attendance_id=?");
        if ($update->execute([$date, $time_in, $time_out, $hours, $hours_late, $attendance_id])) {
            $success = "Record updated successfully.";

            $admin_id = $_SESSION['user_id'] ?? 'unknown';
            $admin_name = $_SESSION['username'] ?? 'Unknown Admin';

            logTransaction($pdo, $admin_id, $admin_name, "Updated attendance record ID: $attendance_id", $admin_name);

            $new_values_array = [
                'date' => $date,
                'time_in' => $time_in,
                'time_out' => $time_out,
                'hours' => $hours,
                'hours_late' => $hours_late
            ];

            $changed_old = [];
            $changed_new = [];

            foreach ($old_values_array as $field => $old_val) {
                $new_val = $new_values_array[$field];
                if ($old_val != $new_val) {
                    $changed_old[$field] = $old_val;
                    $changed_new[$field] = $new_val;
                }
            }

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
        <form method="post" id="attendanceForm">

        <div class="mb-3">
  <label class="form-label">Name</label>
  <input type="text" class="form-control" value="<?= htmlspecialchars($record['full_name'] ?? 'Unknown') ?>" readonly>
</div>


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
    <label class="form-label">Hours Late</label>
    <input type="number" id="hours_late" name="hours_late" step="0.01" class="form-control" value="<?= htmlspecialchars($record['hours_late'] ?? 0) ?>" readonly>
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
  const SCHEDULE_START = "<?= $schedule_start ?>";
</script>
<script>
  function calculateHours() {
  const date = document.querySelector('input[name="date"]').value;
  const timeIn = document.getElementById("time_in").value;
  const timeOut = document.getElementById("time_out").value;
  const hoursField = document.getElementById("hours");
  const hoursLateField = document.getElementById("hours_late");

  if (date && timeIn && timeOut) {
    const start = new Date(`${date}T${timeIn}`);
    const end = new Date(`${date}T${timeOut}`);

    let diff = (end - start) / 1000 / 60 / 60;
    if (diff < 0) diff += 24; 

    hoursField.value = diff.toFixed(2);

    const expected = new Date(`${date}T${SCHEDULE_START}`);

    const lateDiff = (start - expected) / 1000 / 60 / 60;

    hoursLateField.value = lateDiff > 0 ? lateDiff.toFixed(2) : "0.00";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const timeIn = document.getElementById("time_in");
  const timeOut = document.getElementById("time_out");

  timeIn.addEventListener("change", calculateHours);
  timeOut.addEventListener("change", calculateHours);
});
</script>

<script src="/ojtform/autologout.js"></script>
</body>
</html>
