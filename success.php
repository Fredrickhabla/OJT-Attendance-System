<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

/* ── DB connection ────────────────────────── */
$host = "localhost";
$dbname = "ojtform";
$user  = "root";
$pass  = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT * FROM attendance_records
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
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
  <title>Submission Successful</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('images/cover.jpg') no-repeat center/cover fixed;
      font-family: Arial, sans-serif;
      padding-top: 70px;
    }
    .navbar-glass {
      background: rgba(255,255,255,.85);
      backdrop-filter: blur(6px);
      box-shadow: 0 2px 6px rgba(0,0,0,.1);
    }
    .card {
      max-width: 750px;
      margin: 30px auto;
      padding: 20px;
      border: 1px solid #d4edda;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.05);
      background: #fff;
    }
    .label {
      font-weight: bold;
    }
    .signature-img {
      max-width: 250px;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-top: 10px;
      cursor: pointer;
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

<!-- Success + summary -->
<div class="container">
  <div class="card">
    <div class="alert alert-success text-center">
      <h4 class="alert-heading mb-1">
        <i class="bi bi-check-circle-fill text-success"></i> Thank you!
      </h4>
      <p class="mb-0">Your attendance has been successfully submitted.</p>
    </div>

    <?php if ($record): ?>
      <h5 class="text-success mb-4 text-center">
        <i class="bi bi-journal-text"></i> Attendance Summary
      </h5>
      <p><span class="label"><i class="bi bi-calendar-event"></i> Date:</span>
         <?= htmlspecialchars($record['date']) ?></p>
      <p><span class="label"><i class="bi bi-brightness-high"></i> Scheduled Time:</span>
         <?= date("g:i A", strtotime($record['time_in'])) ?> – <?= date("g:i A", strtotime($record['time_out'])) ?></p>
      <p><span class="label"><i class="bi bi-hourglass-split"></i> No.&nbsp;of&nbsp;Hours:</span>
         <?= htmlspecialchars($record['hours']) ?></p>
      <p><span class="label"><i class="bi bi-pencil-square"></i> Work Description:</span>
         <?= htmlspecialchars($record['work_description']) ?></p>
      <p><span class="label"><i class="bi bi-pen-fill"></i> E‑Signature:</span><br>
        <?php if (!empty($record['signature']) && file_exists($record['signature'])): ?>
          <img src="<?= htmlspecialchars($record['signature']) ?>"
               class="signature-img"
               alt="Signature"
               data-bs-toggle="modal"
               data-bs-target="#signatureModal"><br>
          <a href="<?= htmlspecialchars($record['signature']) ?>" download class="btn btn-outline-success mt-2">
            <i class="bi bi-download"></i> Download Signature
          </a>
        <?php else: ?>
          <em>No signature uploaded.</em>
        <?php endif; ?>
      </p>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        <i class="bi bi-exclamation-triangle-fill"></i> No attendance record found.
      </div>
    <?php endif; ?>

    <!-- Buttons -->
    <div class="text-center mt-4">
      <a href="attendance_form.php" class="btn btn-primary me-2">
        <i class="bi bi-arrow-left-circle"></i> Submit Another Attendance
      </a>
      <?php if ($record): ?>
        <a href="edit_attendance.php?id=<?= $record['id'] ?>" class="btn btn-warning">
          <i class="bi bi-pencil-fill"></i> Edit Attendance
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Signature Modal -->
<?php if ($record && !empty($record['signature']) && file_exists($record['signature'])): ?>
<div class="modal fade" id="signatureModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">E‑Signature Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="<?= htmlspecialchars($record['signature']) ?>" class="img-fluid" alt="Signature Full Size">
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
