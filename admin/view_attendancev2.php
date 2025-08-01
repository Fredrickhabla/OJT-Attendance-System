<?php
session_start(); 
include('../conn.php');
require_once 'logger.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /ojtform/indexv2.php");
    exit;
}

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
require_once 'logger.php';

$stmt = $pdo->query("
    SELECT ar.attendance_id, ar.trainee_id, ar.date, ar.time_in, ar.time_out, ar.hours, ar.hours_late,
           CONCAT(u.first_name, ' ', u.surname) AS full_name
    FROM attendance_record ar
    LEFT JOIN trainee u ON ar.trainee_id = u.trainee_id
    WHERE u.active = 'Y'
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
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">


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

    .trainee-btn {
      background-color: #14532d;
      color: white;
      padding: 6px 14px;
      border: 1px solid white;
      border-radius: 15px;
      margin-bottom: 10px;
      cursor: pointer;
      font-size: 12px;
    }
  </style>
</head>
<body>

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div>
      <h1>OJT - ACER</h1>
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
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <a href="report.php" class="<?= $current_page == 'report.php' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
          </svg>
          <strong>Report</strong>
        </a>
        <a href="blogadmin.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h7l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13H7m10-4H7m0 8h4" />
            </svg>
            <span>Blogs</span>
        </a>
        <a href="department.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M4 10h16M10 6h4m-7 4v11m10-11v11M12 14v3" />
           </svg>
            <span>Department</span>
        </a>

      </nav>
    </div>
    <div class="logout">
      <a href="/ojtform/logout.php">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="content">
    <div class="topbar" style="
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 18px;
    border: none;
"> Attendance Records  <button class="trainee-btn" data-bs-toggle="modal" data-bs-target="#downloadModal">
    <i class="bi bi-download"></i> Download All DTR
  </button></div>
    <div class="main">
      <div class="table-container">
        <h3 class="text-center text-success mb-4"><i class="bi bi-table"></i> Attendance Records</h3>

        <div class="mb-3 text-end">
 
  <button id="exportCSV" class="btn btn-outline-success btn-sm">
    <i class="bi bi-funnel"></i> Export Filtered CSV
  </button>
</div>

        <?php if ($records): ?>
        <div class="table-responsive">
          <table id="attendanceTable" class="table table-bordered table-striped">
            <thead class="table-success">
              <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours</th>
                <th>Hours Late</th>
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
                    if (!empty($row['time_out']) && $row['time_out'] !== '00:00:00') {
    $timeOut = DateTime::createFromFormat('H:i:s', $row['time_out']);
    echo $timeOut ? $timeOut->format('g:i A') : htmlspecialchars($row['time_out']);
} else {
    echo '—'; // 
}

                  ?>
                </td>
                <td><?= htmlspecialchars($row['hours']) ?></td>
                <td><?= htmlspecialchars($row['hours_late']) ?></td>
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

        <div class="mt-4 text-end">
          <button type="button" class="btn btn-primary" onclick="window.location.href='report.php'">
            <i class="bi bi-arrow-left"></i> Back to Report
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Download Modal -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="download_dtr.php" method="GET" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="downloadModalLabel">Download DTR</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="start_date" class="form-label">Start Date</label>
          <input type="date" class="form-control" name="start_date" id="start_date" required>
        </div>
        <div class="mb-3">
          <label for="end_date" class="form-label">End Date</label>
          <input type="date" class="form-control" name="end_date" id="end_date" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Download</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  $(document).ready(function () {
  
    const table = $('#attendanceTable').DataTable({
      "order": [[1, "desc"]],
      "pageLength": 10
    });

    $('#exportCSV').on('click', function () {
      const headers = ['Name', 'Date', 'Time In', 'Time Out', 'Hours', 'Hours Late'];
      const rows = table.rows({ search: 'applied' }).nodes(); 
      const csvData = [headers.join(',')];

      rows.each(function (row) {
        const cells = $(row).find('td').toArray();
        const rowData = cells.slice(0, 6).map(td => `"${$(td).text().trim()}"`);
        csvData.push(rowData.join(','));
      });

      if (csvData.length === 1) {
        alert("No matching records found to export.");
        return;
      }

      const blob = new Blob([csvData.join('\n')], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'filtered_attendance.csv';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });

    document.querySelectorAll('.signature-img').forEach(function (img) {
      img.addEventListener('click', function () {
        const src = this.getAttribute('src');
        document.getElementById('signatureModalImg').src = src;
        document.getElementById('downloadSignatureBtn').href = src;
        const modal = new bootstrap.Modal(document.getElementById('signatureModal'));
        modal.show();
      });
    });
  });
</script>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="/ojtform/autologout.js"></script>
</body>
</html>
