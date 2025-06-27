<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin'])) {
    header("Location: /ojtform/indexv2.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: view_attendancev2.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM attendance_records WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    echo "Attendance record not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $hours = $_POST['hours'];
    $work_description = $_POST['work_description'];
    $signature_path = $_POST['existing_signature'] ?? $record['signature'];

    if (!empty($_FILES['signature']['name']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = time() . "_" . basename($_FILES['signature']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['signature']['tmp_name'], $target_path)) {
            $signature_path = $target_path;
        }
    }

    $update = $pdo->prepare("UPDATE attendance_records SET 
        date = ?, time_in = ?, time_out = ?,  
        hours = ?, work_description = ?, signature = ? WHERE id = ?");
    $update->execute([
        $date, $time_in, $time_out,
        $hours, $work_description, $signature_path, $id
    ]);

    header("Location: view_attendancev2.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Attendance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
      color: #333;
      font-family: Arial, sans-serif;
      margin: 0;
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
    .acerlogo {
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
    }
    .nav a:hover {
      background-color: #14532d;
    }
    .nav svg {
      margin-right: 8px;
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
    }
    .logout a:hover {
      background-color: #2c6b11;
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
    }
    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }
    .edit-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
      max-width: 600px;
      margin: auto;
    }
    .signature-preview {
      max-width: 200px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-bottom: 10px;
    }
    .text-time {
      font-size: 0.9rem;
      color: #555;
    }
  </style>
</head>
<body>
<div class="layout">
<aside class="sidebar">
    <div>
      <h1 class="acerlogo"><strong>OJT - ACER</strong></h1>
      <div class="menu-label">Menu</div>
      <nav class="nav">
        <a href="dashboardv2.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 4l9 5.75V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z" />
          </svg>
          Dashboard
        </a>
        <a href="trainee.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Trainee
        </a>
        <a href="coordinator.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7m0-7l-9-5m9 5l9-5" />
          </svg>
          Coordinator
        </a>
        <a href="report.php">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
          </svg>
          Report
        </a>
      </nav>
    </div>
    <div class="logout">
      <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </aside>

  <div class="content">
    <div class="topbar">Edit Attendance</div>
    <div class="main">
      <div class="edit-container">
        <h4 class="text-center text-success mb-4"><i class="bi bi-pencil-square"></i> Edit Attendance Record</h4>
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($record['date']) ?>" required>
          </div>
          <div class="mb-3">
            <label>Time In</label>
            <input type="time" name="time_in" class="form-control" value="<?= date('H:i', strtotime($record['time_in'])) ?>" required>
            <div class="text-time">Current: <?= date('g:i A', strtotime($record['time_in'])) ?></div>
          </div>
          <div class="mb-3">
            <label>Time Out</label>
            <input type="time" name="time_out" class="form-control" value="<?= date('H:i', strtotime($record['time_out'])) ?>" required>
            <div class="text-time">Current: <?= date('g:i A', strtotime($record['time_out'])) ?></div>
          </div>
          <div class="mb-3">
            <label>Total Hours</label>
            <input type="number" step="0.1" name="hours" class="form-control" value="<?= htmlspecialchars($record['hours']) ?>" required>
          </div>
          <div class="mb-3">
            <label>Work Description</label>
            <textarea name="work_description" class="form-control" rows="4" required><?= htmlspecialchars($record['work_description']) ?></textarea>
          </div>
          <div class="mb-3">
            <label>E-Signature (optional)</label><br>
            <?php if (!empty($record['signature']) && file_exists($record['signature'])): ?>
              <img src="<?= htmlspecialchars($record['signature']) ?>" class="signature-preview" alt="Current Signature"><br>
              <input type="hidden" name="existing_signature" value="<?= htmlspecialchars($record['signature']) ?>">
            <?php endif; ?>
            <input type="file" name="signature" class="form-control mt-2" accept="image/*">
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Update Record</button>
            <a href="view_attendancev2.php" class="btn btn-secondary mt-2"><i class="bi bi-arrow-left-circle"></i> Back to Attendance</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
