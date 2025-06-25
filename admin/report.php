<?php
session_start();
if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports - OJT Attendance Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
     * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    body {
      background-color: #f4f6f9;
      color: #333;
    }
    .container {
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
    .acerlogo {
      text-align: center;
      font-size: 20px;
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
      text-align: left;
    }


    .main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .report-box {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(4px);
      border: 1px solid #c8e6c9;
      padding: 50px;
      border-radius: 16px;
      text-align: center;
      box-shadow: 0 8px 30px rgba(0,0,0,0.08);
      max-width: 500px;
      width: 100%;
    }

    .report-box h2 {
      font-size: 26px;
      color: #2e7d32;
      margin-bottom: 30px;
    }

    .report-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 16px 20px;
      width: 100%;
      max-width: 400px;
      margin: 12px auto;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      text-decoration: none;
      color: white;
      transition: 0.2s ease;
    }

    .btn-green {
      background-color: #43a047;
    }

    .btn-green:hover {
      background-color: #2e7d32;
    }

    .btn-blue {
      background-color: #1976d2;
    }

    .btn-blue:hover {
      background-color: #0d47a1;
    }

    .report-btn i {
      font-size: 20px;
    }

    
  </style>
</head>
<body>

<div class="container">
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
    <div class="topbar">Reports & Tools</div>
    <div class="main">
      <div class="report-box">
        <h2>Access Report Pages</h2>

        <a href="view_attendancev2.php" class="report-btn btn-green">
          <i class="bi bi-calendar-check-fill"></i> View Attendance Records
        </a>

        <a href="manage_users.php" class="report-btn btn-blue">
          <i class="bi bi-people-fill"></i> Manage Users
        </a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
