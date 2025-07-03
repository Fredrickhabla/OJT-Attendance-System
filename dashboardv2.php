

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.min.css">
</head>
<style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', sans-serif;
}

body {
  background: #f9f9f9;
  color: #111;
  height: 100vh;
}

.dashboard {
  display: flex;
  height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: #3b7c1b;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 20px 0;
}

.profile-section {
  text-align: center;
  padding: 10px 0 20px;
}

.profile-pic {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 50%;
  margin-bottom: 10px;
}

.profile-section h2 {
  font-size: 1rem;
}

.profile-section p {
  font-size: 0.9rem;
  opacity: 0.9;
}

.separator {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.4);
  margin: 10px 20px;
}

.nav-menu ul {
  list-style: none;
  padding: 0 20px;
}

.nav-menu li {
  margin-bottom: 16px;
}

.nav-menu a {
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  border-radius: 6px;
  transition: background 0.3s;
}

.nav-menu a:hover {
  background: #2f6a13;
}

.logout {
    margin-top: auto;
  padding: 20px;
}

.logout a {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  text-decoration: none;
  padding: 8px;
  border-radius: 6px;
  transition: background 0.3s;
}

.logout a:hover {
  background: #2f6a13;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 40px;
  background: #f9f9f9;
}

.main-content h1 {
  font-size: 1.8rem;
  margin-bottom: 24px;
}

.cards-3 {
  display: flex;
  gap: 24px;
  align-items: stretch;
  height: 90%;
}

.left-col {
  display: flex;
  flex-direction: column;
  gap: 24px;
  flex: 1;
  
}

/* Box 1 (shorter card) */
.card.short-card {
  height: 40%;
}

/* Box 2 (slightly taller) */
.card.tall-card {
  height: 60%;
}

/* Right side card */
.card.wide {
 flex: 1.7;
  display: flex;
  flex-direction: column;
  height: 100%;
}
.table-wrapper {
   max-height: 500px;
    overflow-y: auto;
}

.card {
  border: 2px solid #3b7c1b;
  border-radius: 20px;
  height: auto;
  background-color: white;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: start;
}

.card-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
  font-size: 0.95rem;
  
}


.card-content .row {
  display: flex;
  justify-content: space-between;
}

/* Optional: Force a fixed height on the card for consistency */
.card-header {
  font-weight: bold;
  color: #3b7c1b;
  font-size: 1rem;
  padding-bottom: 8px;
}

.dtr-table {
     width: 100%;
    border-collapse: collapse;
}

.dtr-table th,
.dtr-table td {
   padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
}

.dtr-table th {
  background-color: #f0f5eb;
  color: #3b7c1b;
  font-weight: 600;
}

.dtr-table tbody tr:hover {
  background-color: #f9f9f9;
}

.card.tall-card {
  flex: 2;
  display: flex;
  flex-direction: column;
  padding: 0.5rem;
}

.calendar-container {
  width: 100%;
  height: 350px; /* Adjust to fit */
  overflow: hidden;
  border-radius: 8px;
  background-color: #fff;
}

.fc {
  font-size: 0.75rem; /* Smaller text in calendar */
}

canvas {
  display: block;
  margin-bottom: 16px;
}

.progress-wrapper {
  display: flex;
  align-items: center;
  gap: 5px;
  justify-content: center;
}

canvas {
  display: block;
}

.progress-text div {
  margin-bottom: 10px;
  font-size: .95rem;
}

.progress-text strong {
  color: #000;
  margin-right: 5px;
}


    </style>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/9131/9131529.png" alt="Profile" class="profile-pic" />
        <h2>Raymond Dioses</h2>
        <p>raymond.dioses@gmail.com</p>
      </div>
      <hr class="separator" />
      <nav class="nav-menu">
  <ul>
    <li>
      <a href="#">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M3 9L12 2l9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        DASHBOARD
      </a>
    </li>
    <li>
      <a href="#">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 4h16v16H4z"/>
          <line x1="8" y1="2" x2="8" y2="22"/>
          <line x1="16" y1="2" x2="16" y2="22"/>
        </svg>
        ATTENDANCE FORM
      </a>
    </li>
    <li>
      <a href="#">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M4 21v-2a4 4 0 0 1 3-3.87"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        PROFILE
      </a>
    </li>
    <li>
      <a href="#">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
          <path d="M4 4.5A2.5 2.5 0 0 1 6.5 7H20v13H6.5A2.5 2.5 0 0 1 4 17.5z"/>
        </svg>
        BLOG
      </a>
    </li>
  </ul>
</nav>
      <hr class="separator" />
      <div class="logout">
  <a href="#">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
      <polyline points="16 17 21 12 16 7"/>
      <line x1="21" y1="12" x2="9" y2="12"/>
    </svg>
    Logout
  </a>
</div>

    </aside>

    <!-- Main Content -->
    <main class="main-content">
  <h1>PERSONAL PROGRESS</h1>
  <div class="cards-3">
    <!-- Left Column -->
    <div class="left-col">
      <div class="card short-card progress-card">
  <div class="progress-wrapper">
    <canvas id="progressCircle" width="150" height="150"></canvas>
    <div class="progress-text">
      <div><strong>Required Time:</strong> 240 Hours</div>
      <div><strong>Completed:</strong> 200 Hours</div>
      <div><strong>Time Left:</strong> 40 Hours</div>
    </div>
  </div>
</div>

      <div class="card tall-card">
        <div class="card tall-card">
  <div class="card-header"></div>
  <div id="calendar" class="calendar-container"></div>
</div>
      </div>
    </div>

    <!-- Right Column (Daily Time Record) -->
   <div class="card wide">
  <div class="card-header">Daily Time Record</div>
  <div class="table-wrapper">
    <table class="dtr-table" id="dtrTable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Total Hours</th>
        </tr>
      </thead>
      <tbody>
        <!-- JavaScript will populate rows -->
      </tbody>
    </table>
  </div>
</div>
 

  

  </tbody>
    </table>
  </div>
</div>

    </div>
  </div>
</main>

  </div>
</body>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      contentHeight: 350,  // Shrink vertical size
      aspectRatio: 1.5,    // Wider layout
      headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: ''
      }
    }).render();
  });

    const data = [
    { name: "Raymond Dioses", date: "July 1, 2025", timeIn: "08:00 AM", timeOut: "05:00 PM", total: "9" },
    { name: "Raymond Dioses", date: "July 2, 2025", timeIn: "08:15 AM", timeOut: "05:10 PM", total: "8.9" },
    { name: "Raymond Dioses", date: "July 3, 2025", timeIn: "08:05 AM", timeOut: "04:55 PM", total: "8.8" },
    // Blank rows for layout
    ...Array.from({ length: 12 }, (_, i) => ({ name: "Raymond Dioses", date: `July ${4 + i}, 2025`, timeIn: "", timeOut: "", total: "" }))
  ];

  const tableBody = document.querySelector('#dtrTable tbody');

  data.forEach(entry => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${entry.name}</td>
      <td>${entry.date}</td>
      <td>${entry.timeIn || "&nbsp;"}</td>
      <td>${entry.timeOut || "&nbsp;"}</td>
      <td>${entry.total || "&nbsp;"}</td>
    `;
    tableBody.appendChild(row);
  });

  function drawProgressCircle(canvasId, completedHours, totalHours) {
  const canvas = document.getElementById(canvasId);
  const ctx = canvas.getContext("2d");
  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = 50;
  const lineWidth = 10;
  const percent = completedHours / totalHours;

  // Background circle
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
  ctx.strokeStyle = "#e6e6e6";
  ctx.lineWidth = lineWidth;
  ctx.stroke();

  // Progress arc
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, -0.5 * Math.PI, (2 * Math.PI * percent) - 0.5 * Math.PI);
  ctx.strokeStyle = "#3b7c1b";
  ctx.lineWidth = lineWidth;
  ctx.lineCap = "round";
  ctx.stroke();

  // Text
  ctx.font = "16px Segoe UI";
  ctx.fillStyle = "#3b7c1b";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillText(`${Math.round(percent * 100)}%`, centerX, centerY);
}

drawProgressCircle("progressCircle", 200, 240);

</script>


</html>
