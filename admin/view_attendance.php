<?php
session_start();
include('connection.php');

if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM attendance_records ORDER BY date DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Attendance - OJT Attendance System</title>
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
    .table-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
    }
    .signature-img {
      max-width: 120px;
      max-height: 60px;
      cursor: pointer;
    }
    .modal-img {
      max-width: 100%;
      height: auto;
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
  <div class="table-container">
    <h3 class="text-center text-success mb-4"><i class="bi bi-table"></i> Attendance Records</h3>

    <div class="mb-3 text-end">
      <a href="export_csv.php" class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export as CSV
      </a>
    </div>

    <?php if ($records): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-success">
          <tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours</th>
            <th>Work Description</th>
            <th>E-Signature Image</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($records as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= date('g:i A', strtotime($row['morning_in'])) ?></td>
            <td><?= date('g:i A', strtotime($row['afternoon_out'])) ?></td>
            <td><?= htmlspecialchars($row['hours']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['work_description'])) ?></td>
            <td>
              <?php if (!empty($row['signature'])): ?>
                <img src="<?= htmlspecialchars($row['signature']) ?>" alt="Signature" class="signature-img"
                     data-bs-toggle="modal" data-bs-target="#modal<?= $row['id'] ?>" title="Click to view">
                
                <!-- Modal -->
                <div class="modal fade" id="modal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel<?= $row['id'] ?>">E-Signature Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="<?= htmlspecialchars($row['signature']) ?>" alt="Full Signature" class="modal-img">
                      </div>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <span class="text-muted">No signature</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="edit_attendance.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="delete_attendance.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Are you sure you want to delete this record?');">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="text-center">No attendance records found.</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
