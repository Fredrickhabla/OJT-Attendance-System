<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - OJT ACER</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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

    /* Main */
    .main {
      flex: 1;
      padding: 28px;
      overflow-y: auto;
      margin-left: 30px;
      margin-right: 30px;
      margin-top:10px;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
      margin-bottom: 30px;
      height: 200px;

    }

    .card {
      margin: 10px;
      background: white;
      border: 2px solid #166534;
      border-radius: 8px;
      padding: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card .info {
      text-align: right;
    }

    .card .title {
      font-size: 16px;
      font-weight: bold;
      color: #166534;
      margin-right : 10px;
      padding: 10px;
    }

    .card .value {
      font-size: 32px;
      font-weight: bold;
      margin-right : 10px;
      padding: 10px;
    }

    .cardlogo {
        width: 80px;
        height: 80px;
        margin-left: 15px;
    }

    .cardlogo1 {
        width: 90px;
        height: 90px;
        margin-left: 15px;
    }

    .chart-container {
        background: white;
        border: 2px solid #166534;
        border-radius: 8px;
        padding: 20px;
        height: 67%;
        margin-left: 10px;
        margin-right: 10px;
        
        
    }

    .acerlogo {
        text-align: center;
        font-size: 20px;
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
    </nav>

    <div class="logout">
      <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main">

    <!-- Cards -->
    <div class="cards">
      <div class="card">
        <img class="cardlogo" src="/ojtform/images/user.png" alt="Trainee Icon">
        <div class="info">
          <div class="title">TRAINEE</div>
          <div class="value">40</div>
        </div>
      </div>

      <div class="card">
        <img class="cardlogo1" src="/ojtform/images/multiple_user.png" alt="Trainee Icon">
        <div class="info">
          <div class="title">COORDINATOR</div>
          <div class="value">100</div>
        </div>
      </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
      <canvas id="barChart"></canvas>
    </div>

    <script>
      const ctx = document.getElementById('barChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['TRAINEE', 'COORDINATOR'],
          datasets: [{
            label: 'OJT Count',
            data: [40, 100],
            backgroundColor: ['#44830f', '#14532d'],
            borderRadius: 10,
            barThickness: 400
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 120,
              ticks: {
                stepSize: 20,
                callback: value => value + '%'
              },
              grid: {
                color: '#ddd'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    </script>

  </main>
</div>

</body>
</html>
