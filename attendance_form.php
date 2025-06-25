<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>OJT Attendance Sheet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
  body {
    background: url('images/cover.jpg') no-repeat center/cover fixed;
    font-family: Arial, sans-serif;
    margin: 0;
    padding-top: 70px;
  }
  .navbar-glass {
    background: rgba(255, 255, 255, .85);
    backdrop-filter: blur(6px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, .1);
  }
  .container-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 70px);
  }
  .attendance-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
    width: 100%;
    max-width: 1000px;
    padding: 30px;
    overflow: auto;
  }
  h2 {
    color: #2e7d32;
    text-align: center;
    margin-bottom: 30px;
    font-weight: bold;
  }
  table th, table td {
    vertical-align: middle !important;
  }
  th {
    background: #e8f5e9 !important;
    color: #2e7d32;
  }
  .form-control {
    border-radius: 8px;
  }
  .btn-success {
    background: #2e7d32;
    border: none;
    padding: 10px 30px;
    border-radius: 8px;
    font-size: 16px;
  }
  .btn-success:hover {
    background: #1b5e20;
  }
  .file-label {
    font-weight: 500;
    color: #2e7d32;
  }
  @media (max-width: 768px) {
    .attendance-card {
      padding: 20px;
    }
    th, td {
      font-size: 12px;
    }
  }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="#">OJT ATTENDANCE SYSTEM</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLinks">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navLinks">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-success fw-semibold" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-wrapper">
  <div class="attendance-card">
    <h2>OJT Attendance System</h2>

    <form action="save_attendance.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">

      <div class="table-responsive mb-4">
        <table class="table table-bordered text-center mb-0">
          <thead>
            <tr>
              <th>Date</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>No. of Hours</th>
              <th>Work Description</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input type="date" name="date" class="form-control" required></td>
              <td><input type="time" name="time_in" class="form-control" required></td>
              <td><input type="time" name="time_out" class="form-control" required></td>
              <td><input type="number" name="hours" step="0.1" class="form-control" required></td>
              <td><input type="text" name="work_description" class="form-control" required></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mb-4">
        <label for="signature" class="form-label file-label"><strong>E-Signature Image</strong></label>
        <input type="file" class="form-control" name="signature" id="signature" accept="image/*" required>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-success">Submit Attendance</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
