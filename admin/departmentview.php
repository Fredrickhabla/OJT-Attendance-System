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

    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      background-color: #14532d;
      color: white;
      padding: 10px 24px;
      font-size: 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
      height: 60px;
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

 

.main {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 10px;
   overflow-y: auto;
}

.header {
  background-color: #065f46;
  padding: 1rem;
  color: white;
  font-size: 1.5rem;
  font-weight: 600;
}

.cards {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.5rem;
}

.card {
  flex: 1;
  border: 2px solid #16a34a; /* Tailwind's green-600 */
  padding: 1.5rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: 14px;
  height: 130px;
}

.icon {
  font-size: 3rem;
  color: #16a34a;
}

.card-label {
  margin-top: 0.5rem;
  font-size: 1.25rem;
  font-weight: bold;
}

.table-section {
  padding: 0 1.5rem 1.5rem;
}

/* Table container */
table {
  width: 100%;
  border-collapse: collapse;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

  border: 1px solid #16a34a;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  border-radius: 8px;
    background-color: transparent; /* Changed from #ffffff */

}

/* Table header row */
thead {
  background-color:rgb(228, 240, 217);
  color: rgb(59, 124, 27);
  font-weight: bold;
  
}

/* Table cells */
th, td {
  padding: 12px 16px;
  text-align: left;
  font-size: 14px;
  
  border: 1px solid #16a34a;
}



/* Status badges */
.badge {
  padding: 0.3rem 0.7rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-block;
}


.badge.Active {
  background-color: #dcfce7;
  color: #15803d;
}

.badge.Completed {
  background-color: #bbf7d0;
  color: #166534;
}

.badge.Ongoing {
  background-color: #e0f2fe;
  color: #0369a1;
}



  </style>
</head>
<body>

<div class="container">
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
    </div>
    <div class="logout">
      <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="content">
    <div class="topbar">Accounting</div>
    <div class="main">

   <section class="cards">
  <div class="card">
    <!-- 👤 Trainee Icon -->
    <!-- Better Trainee Icon -->
<svg xmlns="http://www.w3.org/2000/svg" height="60px" class="icon" viewBox="0 0 24 24" fill="#16a34a">
  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
</svg>

    <span class="card-label">5 Trainee</span>
  </div>

  <div class="card">
    <!-- 🔄 Ongoing Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="1 4 1 10 7 10" />
      <polyline points="23 20 23 14 17 14" />
      <path d="M20.49 9A9 9 0 0 0 5.51 5M3 14a9 9 0 0 0 15.49 4" />
    </svg>
    <span class="card-label">3 Ongoing</span>
  </div>

  <div class="card">
    <!-- ✅ Completed Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 12l2 2l4 -4" />
      <circle cx="12" cy="12" r="10" />
    </svg>
    <span class="card-label">2 Completed</span>
  </div>
</section>

<div class="table-section">
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>School</th>
        <th>Required Time</th>
        <th>Completed Time</th>
        <th>Status</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      <!-- JavaScript will populate rows here -->
    </tbody>
  </table>
</div>

<script>
  const tableBody = document.querySelector("tbody");

  const trainees = [
    {
      name: "Fredrick Habla",
      school: "PLM",
      requiredTime: 240,
      completedTime: 120,
      status: "Active"
    },
    {
      name: "Jane Dela Cruz",
      school: "UST",
      requiredTime: 240,
      completedTime: 240,
      status: "Completed"
    },
    {
      name: "Carlos Santos",
      school: "Adamson",
      requiredTime: 240,
      completedTime: 80,
      status: "Ongoing"
    }
  ];

  tableBody.innerHTML = ""; // Clear existing content

  trainees.forEach((trainee) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${trainee.name}</td>
      <td>${trainee.school}</td>
      <td>${trainee.requiredTime}</td>
      <td>${trainee.completedTime}</td>
      <td><span class="badge">${trainee.status}</span></td>
      <td></td>
    `;
    tableBody.appendChild(row);
  });

  // Optionally add 7 empty rows to match layout
  for (let i = 0; i < 7; i++) {
    const emptyRow = document.createElement("tr");
    emptyRow.innerHTML = `<td colspan="6">&nbsp;</td>`;
    tableBody.appendChild(emptyRow);
  }
</script>
