<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accounting - OJT ACER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

    .main-content {
      flex: 1;
      padding: 24px;
      overflow-y: auto;
    }

    .card-wrapper {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .card {
      border: 2px solid #3a7d0f;
      border-radius: 12px;
      background-color: white;
      padding: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .card .icon {
      font-size: 32px;
      color: #3a7d0f;
    }

    .card .label {
      margin-top: 8px;
      font-weight: 500;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #3a7d0f;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .upload-btn {
      display: inline-block;
      margin-bottom: 16px;
      padding: 10px 16px;
      background-color: #3a7d0f;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
    }

    .upload-btn:hover {
      background-color: #2c6b11;
    }
  </style>
</head>
<body>
<div class="container">
  <!-- Sidebar -->
<aside class="sidebar">
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
      <a href="blogadmin.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h7l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13H7m10-4H7m0 8h4" />
            </svg>
            <span>Blogs</span>
        </a>
      <a href="department.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h7l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13H7m10-4H7m0 8h4" />
            </svg>
            <span>Department</span>
        </a>
    </nav>

    <div class="logout">
      <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </a>
    </div>
  </aside>


  <!-- Main -->
  <div style="flex:1; display:flex; flex-direction:column;">
    <div class="topbar">
      Accounting Department
    </div>

    <div class="main-content">
      <!-- Statistics -->
      <div class="card-wrapper">
        <div class="card">
          <i class="fas fa-users icon"></i>
          <span class="label">5 Trainees</span>
        </div>
        <div class="card">
          <i class="fas fa-file-alt icon"></i>
          <span class="label">12 Reports Submitted</span>
        </div>
        <div class="card">
          <i class="fas fa-check-circle icon"></i>
          <span class="label">3 Completed</span>
        </div>
      </div>

      <!-- Upload Button -->
      <a href="#" class="upload-btn"><i class="bi bi-upload"></i> Upload Report</a>

      <!-- Trainee Table -->
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>School</th>
            <th>Hours Completed</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Juan Dela Cruz</td>
            <td>ABC University</td>
            <td>120</td>
            <td>Active</td>
            <td>
              <a href="#">View</a> |
              <a href="#">Edit</a>
            </td>
          </tr>
          <tr>
            <td>Maria Santos</td>
            <td>XYZ College</td>
            <td>150</td>
            <td>Completed</td>
            <td>
              <a href="#">View</a> |
              <a href="#">Edit</a>
            </td>
          </tr>
          <!-- More rows as needed -->
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
