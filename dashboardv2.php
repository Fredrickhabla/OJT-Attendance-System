<?php
session_start();
$_SESSION['flash'] = $_SESSION['flash'] ?? [];
require_once 'connection.php';
if (!isset($_SESSION["user_id"])) {
    header("Location: indexv2.php");
    exit();
}

$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) &&
   (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: indexv2.php?timeout=1"); 
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();


$user_id = $_SESSION["user_id"] ?? null;
if (!$user_id) {
    die("No session user_id found.");
}
require_once 'logger.php';


$requiredHours = 0;
$completedHours = 0;
$remainingHours = 0;
$percentage = 0;
$trainee_id = null;
$attendanceData = [];
$full_name = "Unknown User";
$email = "unknown@example.com"; 

$profile_picture = "/ojtform/images/placeholder.jpg"; 

$traineeQuery = $conn->prepare("
    SELECT trainee_id, required_hours, email, profile_picture, CONCAT(first_name, ' ', surname) AS full_name 
    FROM trainee 
    WHERE user_id = ?
");
$traineeQuery->bind_param("s", $user_id);
$traineeQuery->execute();
$traineeResult = $traineeQuery->get_result();

if ($traineeRow = $traineeResult->fetch_assoc()) {
    $trainee_id = $traineeRow["trainee_id"];
    $requiredHours = (int) $traineeRow["required_hours"];
    $full_name = $traineeRow["full_name"];
    $email = $traineeRow["email"];
    $profile_picture = !empty($traineeRow["profile_picture"]) ? $traineeRow["profile_picture"] : $profile_picture;

    $username = "UnknownUsername";
    $userQuery = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $userQuery->bind_param("s", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    if ($userRow = $userResult->fetch_assoc()) {
        $username = $userRow["username"];
    }
    $userQuery->close();


    if (isset($_GET['download']) && $_GET['download'] === 'dtr') {
        $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        logTransaction($pdo, $user_id, $full_name, "Downloaded Daily Time Record", $username);
    }
}

$traineeQuery->close();

if ($trainee_id) {
    $completedQuery = $conn->prepare("SELECT SUM(hours) as total_hours FROM attendance_record WHERE trainee_id = ?");
    $completedQuery->bind_param("s", $trainee_id);
    $completedQuery->execute();
    $completedResult = $completedQuery->get_result();
    if ($completedRow = $completedResult->fetch_assoc()) {
        $completedHours = isset($completedRow['total_hours']) ? (float) $completedRow['total_hours'] : 0;
        $remainingHours = max(0, $requiredHours - $completedHours);
        $percentage = ($requiredHours > 0) ? round(($completedHours / $requiredHours) * 100) : 0;
    }
    $completedQuery->close();


    $attendanceQuery = $conn->prepare("
    SELECT date, time_in, time_out, hours, status, hours_late
    FROM attendance_record 
    WHERE trainee_id = ? 
    ORDER BY date DESC
");
    $attendanceQuery->bind_param("s", $trainee_id);
    $attendanceQuery->execute();
    $attendanceResult = $attendanceQuery->get_result();

    while ($row = $attendanceResult->fetch_assoc()) {
        $attendanceData[] = $row;
    }

    $attendanceQuery->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    date_default_timezone_set('Asia/Manila');
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    $stmt = $conn->prepare("SELECT * FROM attendance_record WHERE trainee_id = ? AND date = ?");
    $stmt->bind_param("ss", $trainee_id, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();

    if (isset($_POST['time_in'])) {
    if ($existing) {
        $_SESSION['flash'][] = "You have already timed in today.";
    } else {
        $attendance_id = uniqid("attn_");
        $time_out = '00:00:00';
        $hours = 0;

        // --- Get trainee schedule
        $schedStmt = $conn->prepare("SELECT schedule_start FROM trainee WHERE trainee_id = ?");
        $schedStmt->bind_param("s", $trainee_id);
        $schedStmt->execute();
        $schedResult = $schedStmt->get_result();
        $schedRow = $schedResult->fetch_assoc();
        $schedStmt->close();

        $schedStart = new DateTime($schedRow['schedule_start']);
        $timeIn = new DateTime($currentTime);
        $status = "present";
        $hoursLate = 0;

        if ($timeIn > $schedStart) {
            $status = "late";
            $hoursLate = round(($timeIn->getTimestamp() - $schedStart->getTimestamp()) / 3600, 2);
        }

        $insertStmt = $conn->prepare("
            INSERT INTO attendance_record (attendance_id, trainee_id, date, time_in, time_out, hours, status, hours_late)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insertStmt->bind_param("sssssdss", $attendance_id, $trainee_id, $currentDate, $currentTime, $time_out, $hours, $status, $hoursLate);
        $insertStmt->execute();
        $insertStmt->close();

        $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Transaction log
logTransaction($pdo, $user_id, $full_name, "Trainee timed in at $currentTime with status: $status", $username);

// Audit log
logAudit($pdo, $user_id, "Time In", $currentTime, null, $username);


        $_SESSION['flash'][] = "Time in recorded at $currentTime. Status: $status.";
    }
}

if (isset($_POST['time_out'])) {
    if (!$existing) {
        $_SESSION['flash'][] = "You need to time in first before timing out.";
    } elseif ($existing['time_out'] !== "00:00:00") {
        $_SESSION['flash'][] = "You have already timed out today.";
    } else {
        // Get trainee's schedule
        $schedStmt = $conn->prepare("SELECT schedule_start, schedule_end FROM trainee WHERE trainee_id = ?");
        $schedStmt->bind_param("s", $trainee_id);
        $schedStmt->execute();
        $schedResult = $schedStmt->get_result();
        $schedRow = $schedResult->fetch_assoc();
        $schedStmt->close();

        $schedStart = new DateTime($schedRow['schedule_start']);
        $schedEnd = new DateTime($schedRow['schedule_end']);
        $timeIn = new DateTime($existing['time_in']);
        $timeOut = new DateTime($currentTime);
        $interval = $timeOut->diff($timeIn);
        $totalMinutes = ($interval->h * 60) + $interval->i;
        $hoursWorked = floor($totalMinutes / 60);

        // Deduct 1 hour break if 5 or more hours worked
        if ($hoursWorked >= 5) {
            $hoursWorked -= 1;
        }

        // Only determine if late or present
        $status = "present";
        $hoursLate = 0;
        if ($timeIn > $schedStart) {
            $status = "late";
            $hoursLate = round(($timeIn->getTimestamp() - $schedStart->getTimestamp()) / 3600, 2);
        }

        $updateStmt = $conn->prepare("
            UPDATE attendance_record 
            SET time_out = ?, hours = ?, hours_late = ?, status = ? 
            WHERE trainee_id = ? AND date = ?
        ");
        $updateStmt->bind_param("sdssss", $currentTime, $hoursWorked, $hoursLate, $status, $trainee_id, $currentDate);
        $updateStmt->execute();
        $updateStmt->close();

        $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Transaction log
logTransaction($pdo, $user_id, $full_name, "Trainee timed out at $currentTime with status: $status", $username);

// Audit log
logAudit($pdo, $user_id, "Time Out", $currentTime, $existing['time_in'], $username);


        $_SESSION['flash'][] = "Time out recorded successfully at $currentTime. Status: $status.";
    }
}

    // Always redirect after handling post
    header("Location: dashboardv2.php");
    exit();
}

$conn->close(); 
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.min.css">
  <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

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


.sidebar {
  width: 300px;
  background: #44830f;
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

.card-wide {
  border-radius: 8px;
  height: 100%;
}

.card.short-card {
  height: 40%;
}


.card.tall-card {
  height: 60%;
}


.card.wide {
 flex: 1.7;
  display: flex;
  flex-direction: column;
  min-height: 400px;
  border-radius: 8px;
}
.table-wrapper {
  
  border-radius: 8px;
  overflow-y: auto;
  max-height: 100%;


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

.card-header {
  font-weight: bold;
  color: #3b7c1b;
  font-size: 1rem;
  padding-bottom: 8px;
}

.dtr-table {
     width: 100%;
    border-collapse: collapse;
    
    border-radius: 8px;
    max-height: 100%;
    
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
  max-height: 100%;
}

.card.tall-card {
  flex: 2;
  display: flex;
  flex-direction: column;
  padding: 0.5rem;
}

.calendar-container {
  width: 100%;
  height: 350px; 
  overflow: hidden;
  border-radius: 8px;
  background-color: #fff;
}

.fc {
  font-size: 0.75rem;
  color: blie;
}
  .fc .fc-button {
    background-color: #3b7c1b;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 6px;
    font-weight: bold;
  }

  .fc .fc-prev-button {
    background-color: #3b7c1b;
  }

  .fc .fc-next-button {
    background-color: #3b7c1b;
  }

   
  .fc .fc-button:hover {
    background-color: #2e5e14;
  }

  .fc .fc-button:disabled {
    background-color: #ccc;
    color: #666;
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
  height: 100%;
  width: 100%;
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

.btn-timein, .btn-timeout {
  padding: 8px 16px;
  background-color: #3b7c1b;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-timein:hover, .btn-timeout:hover {
  background-color: #2f5e14;
}

    </style>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
  <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile" class="profile-pic" />
  <h2><?= htmlspecialchars($full_name) ?></h2>
  <p><?= htmlspecialchars($email) ?></p>
</div>

      <hr class="separator" />
      <nav class="nav-menu">
  <ul>
    <li>
      <a href="dashboardv2.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M3 9L12 2l9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        DASHBOARD
      </a>
    </li>
    <li>
      <a href="profiledashboard.php">
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
      <a href="blog.php">
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
 <a href="logout.php" class="logout-link">
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
 <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
  <h1 style="margin: 0;">PERSONAL PROGRESS</h1>
  <div style="display: flex; gap: 10px;">
    <form method="post" action="" style="display: inline;">
      <button type="submit" name="time_in" class="btn-timein">Time In</button>
    </form>
    <form method="post" action="" style="display: inline;">
      <button type="submit" name="time_out" class="btn-timeout">Time Out</button>
    </form>
  </div>
</div>


  <div class="cards-3">
    <!-- Left Column -->
    <div class="left-col">
      <div class="card short-card progress-card">
  <div class="progress-wrapper">
    <canvas class = "progresscircle" id="progressCircle" width="150" height="150"></canvas>
    <div class="progress-text">
       <strong>Required Time:</strong> <?= $requiredHours ?> Hours<br />
    <strong>Completed:</strong> <?= $completedHours ?> Hours<br />
    <strong>Time Left:</strong> <?= $remainingHours ?> Hours
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

   <div class="card wide">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
  <span>Daily Time Record</span>
  <button id="downloadBtn" style="background: none;  border: none; cursor: pointer; margin-right: 10px; color: #3b7c1b;" title="Download DTR">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
      <polyline points="7 10 12 15 17 10"/>
      <line x1="12" y1="15" x2="12" y2="3"/>
    </svg>
  </button>
</div>


  <div class="table-wrapper">
    <table class="dtr-table" id="dtrTable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Date</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Total Hours</th>
          <th>Hours Late</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
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
  <?php if (!empty($_SESSION['flash'])): ?>
  <script>
    <?php foreach ($_SESSION['flash'] as $msg): ?>
      alert("<?= addslashes($msg) ?>");
    <?php endforeach; ?>
  </script>
  <?php $_SESSION['flash'] = []; // Clear messages ?>
<?php endif; ?>

</body>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    contentHeight: 350,
    aspectRatio: 1.5,
    headerToolbar: {
      left: 'prev,next',
      center: 'title',
      right: '',
    },
    events: attendanceEvents 
  });

  calendar.render();
});


    const data = <?= json_encode($attendanceData) ?>;
const userName = <?= json_encode($full_name) ?>;


const today = new Date(); 
today.setHours(0, 0, 0, 0); 



const attendanceEvents = data
  .filter(entry => {
    const entryDate = new Date(entry.date);
    entryDate.setHours(0, 0, 0, 0);
    return entryDate <= today;
  })
  .map(entry => ({
    title: 'Present',
    start: entry.date,
    display: 'auto',
    backgroundColor: '#d1fae5',
    borderColor: '#34d399',
    textColor: '#065f46'
  }));



  const tableBody = document.querySelector('#dtrTable tbody');

if (data.length === 0) {
  const row = document.createElement('tr');
  row.innerHTML = `
    <td colspan="7" style="text-align: center; padding: 220px 50px; color: #888; font-style: italic; border-radius: 8px;">
      No attendance record yet.
    </td>
  `;
  tableBody.appendChild(row);
} else {
  data.forEach(entry => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${userName}</td>
      <td>${entry.date}</td>
      <td>${entry.time_in || "&nbsp;"}</td>
      <td>${entry.time_out || "&nbsp;"}</td>
      <td>${entry.hours || "&nbsp;"}</td>
      <td>${entry.hours_late || "&nbsp;"}</td>
      <td>${entry.status || "&nbsp;"}</td>
    `;
    tableBody.appendChild(row);
  });
}


  function drawProgressCircle(canvasId, completedHours, totalHours) {
  const canvas = document.getElementById(canvasId);
  const ctx = canvas.getContext("2d");
  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  const radius = 50;
  const lineWidth = 10;
  const percent = completedHours / totalHours;

  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
  ctx.strokeStyle = "#e6e6e6";
  ctx.lineWidth = lineWidth;
  ctx.stroke();

  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, -0.5 * Math.PI, (2 * Math.PI * percent) - 0.5 * Math.PI);
  ctx.strokeStyle = "#3b7c1b";
  ctx.lineWidth = lineWidth;
  ctx.lineCap = "round";
  ctx.stroke();

  ctx.font = "16px Segoe UI";
  ctx.fillStyle = "#3b7c1b";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillText(`${Math.round(percent * 100)}%`, centerX, centerY);
}

drawProgressCircle("progressCircle", <?= $completedHours ?>, <?= $requiredHours ?>);

document.getElementById("downloadBtn").addEventListener("click", function () {
  const table = document.getElementById("dtrTable");
  const workbook = XLSX.utils.table_to_book(table, { sheet: "Daily Time Record" });
  XLSX.writeFile(workbook, "DailyTimeRecord.xlsx");

  fetch('log_download.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ action: "download_dtr" })
  })
  .then(response => response.json())
  .then(data => {
    console.log("Log status:", data.status);
  })
  .catch(error => {
    console.error("Logging error:", error);
  });
});


</script>
<script src="autologout.js"></script>

</html>
