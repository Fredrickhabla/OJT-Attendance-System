<?php
session_start();
include('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

// Fetch attendance records joined with user full name
$stmt = $pdo->query("
    SELECT ar.*, CONCAT(u.first_name, ' ', u.surname) AS full_name
    FROM attendance_record ar
    LEFT JOIN trainee u ON ar.trainee_id = u.trainee_id
    ORDER BY ar.date DESC
");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance Records</title>
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
    .table-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
    }
    .signature-img {
      max-width: 120px;
      max-height: 60px;
      display: block;
      cursor: pointer;
    }
  </style>
</head>
<body>

<body>

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div>
      <h1 class="acerlogo">OJT - ACER</h1>
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

  <!-- Main Content -->
  <div class="content">
    <div class="topbar">Attendance Records</div>
    <div class="main">
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
                <th>Name</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours</th>
                <th>Work Description</th>
                <th>View Signature</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($records as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['full_name'] ?: 'Unknown') ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td>
                  <?php
                    if (!empty($row['time_in'])) {
                      $timeIn = DateTime::createFromFormat('H:i:s', $row['time_in']);
                      echo $timeIn ? $timeIn->format('g:i A') : htmlspecialchars($row['time_in']);
                    }
                  ?>
                </td>
                <td>
                  <?php
                    if (!empty($row['time_out'])) {
                      $timeOut = DateTime::createFromFormat('H:i:s', $row['time_out']);
                      echo $timeOut ? $timeOut->format('g:i A') : htmlspecialchars($row['time_out']);
                    }
                  ?>
                </td>
                <td><?= htmlspecialchars($row['hours']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['work_description'])) ?></td>
                <td>
                  <?php if (!empty($row['signature'])): ?>
                    <img src="/ojtform/<?= htmlspecialchars($row['signature']) ?>" 
                        alt="Signature" 
                        class="signature-img mx-auto d-block" 
                        title="Click to enlarge" />
                  <?php else: ?>
                    <span class="text-muted d-block text-center">No signature</span>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <a href="edit_attendancev2.php?attendance_id=<?= urlencode($row['attendance_id']) ?>" class="btn btn-sm btn-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete_attendancev2.php?attendance_id=<?= urlencode($row['attendance_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');" title="Delete">
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

        <!-- Back to Report Button inside the box -->
        <div class="mt-4 text-end">
          <button type="button" class="btn btn-primary" onclick="window.location.href='report.php'">
            <i class="bi bi-arrow-left"></i> Back to Report
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal for Signature Image -->
<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="signatureModalLabel">Signature Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="signatureModalImg" class="img-fluid mb-3" alt="Signature Image">
        <br>
        <a href="#" id="downloadSignatureBtn" class="btn btn-success" download>
          <i class="bi bi-download"></i> Download Signature
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS + Modal Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.querySelectorAll('.signature-img').forEach(function(img) {
    img.addEventListener('click', function(e) {
      const src = this.getAttribute('src');
      const modalImg = document.getElementById('signatureModalImg');
      modalImg.src = src;

      const downloadLink = document.getElementById('downloadSignatureBtn');
      downloadLink.href = src;

      const modal = new bootstrap.Modal(document.getElementById('signatureModal'));
      modal.show();
    });
  });
</script>

</body>

</html>
