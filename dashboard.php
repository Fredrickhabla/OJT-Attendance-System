<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - OJT ACER</title>
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

    /* Sidebar */
    .sidebar {
      width: 300px;
      background-color: #44830f;
      color: white;
      padding: 24px;
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

    /* Main */
    .main {
      flex: 1;
      padding: 32px;
      overflow-y: auto;
      margin-left: 30px;
      margin-right: 30px;
      margin-top:10px;

 
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
      margin-bottom: 48px;
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

    /* Chart */
    .chart-container {
        position: relative;
        height: 60%;
        border-radius: 8px;
        padding: 24px;
        margin: 10px;
    }

    .y-axis {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        font-size: 12px;
        color: #666;
        padding-left: 10px;
    }

    .bars {
        display: flex;
        justify-content: center;  
        align-items: flex-end;
        gap: 80px;
        height: 100%;
        z-index: 1; 
    }

    .bar {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        padding: 0px;
        vertical-align: bottom;
    }

    .bar div {
        background-color: #44830f;
        border-radius: 8px 8px 0 0;
        transition: height 0.3s ease-in-out;
    }

    .bar label {
        margin-top: 8px;
        font-size: 12px;
    }

    .grid-lines {
        position: absolute;
        left: 40px;
        right: 0;
        top: 0;
        bottom: 0;
        pointer-events: none;
        z-index: 0; 
    }

    .grid-line {
        position: absolute;
        width: 100%;
        border-top: 1px solid #ddd;
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
      <a href="#">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 4l9 5.75V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.75z" />
      </svg>
      Dashboard
    </a>
    
    <a href="#">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      Trainee
    </a>
    
    <a href="#">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7m0-7l-9-5m9 5l9-5" />
      </svg>
      Coordinator
    </a>
    
    <a href="#">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
      </svg>
      Report
    </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main">

    <!-- Cards -->
    <div class="cards">
      <div class="card">
        <img class="cardlogo" src="images/user.png" alt="Trainee Icon">
        <div class="info">
          <div class="title">TRAINEE</div>
          <div class="value">40</div>
        </div>
      </div>

      <div class="card">
        <img class="cardlogo1" src="images/multiple_user.png" alt="Trainee Icon">
        <div class="info">
          <div class="title">COORDINATOR</div>
          <div class="value">100</div>
        </div>
      </div>
    </div>

    <!-- Chart -->
<!-- Chart -->
<div class="chart-container" style="background: white; border: 2px solid #166534; position: relative;">
  <!-- Y-axis -->
  <div class="y-axis">
    <div>100%</div>
    <div>80%</div>
    <div>60%</div>
    <div>40%</div>
    <div>20%</div>
    <div>0%</div>
  </div>

  <!-- Bars -->
  <div class="bars" style="position: absolute; bottom: 24px; left: 60px; right: 24px;  top: 24px;">
    <div class="bar">
      <div style="margin-top: 200px; height: 50%; width: 400px; background-color: #44830f; border-radius: 20px 20px 0 0; vertical-align: bottom"></div>
      <label>TRAINEE</label>
    </div>
    <div class="bar">
      <div style="height: 100%; width: 400px; background-color: #44830f; border-radius: 20px 20px 0 0;"></div>
      <label>COORDINATOR</label>
    </div>
  </div>

  <!-- Grid Lines -->
  <div class="grid-lines">
    
    <div class="grid-line" style="top: 20%;"></div>
    <div class="grid-line" style="top: 40%;"></div>
    <div class="grid-line" style="top: 60%;"></div>
    <div class="grid-line" style="top: 80%;"></div>
    <div class="grid-line" style="top: 100%;"></div>
  </div>
</div>
  </main>
</div>

</body>
</html>
