<?php
session_start(); 
include('../connection.php');
require_once '../logger.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinator') {
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

if (isset($_GET['fetch_dtr']) && isset($_GET['trainee_id'])) {
    header('Content-Type: application/json');
    
    $trainee_id = $_GET['trainee_id'];

    $stmt = $conn->prepare("SELECT date, time_in, time_out FROM attendance_record WHERE trainee_id = ?");

    $stmt->bind_param("s", $trainee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $timeIn = new DateTime($row['time_in']);
    $timeOut = new DateTime($row['time_out']);

   
    $interval = $timeIn->diff($timeOut);
    $totalHours = (int)($interval->h + ($interval->i / 60));


    $records[] = [
        'date' => $row['date'],
        'time_in' => $row['time_in'],
        'time_out' => $row['time_out'],
        'total_hours' => number_format($totalHours, 2)
    ];
}

    echo json_encode($records);
    exit;
}


$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}


$coorResult = $conn->query("SELECT coordinator_id, name, email, profile_picture FROM coordinator WHERE user_id = '$user_id'");

if (!$coorResult || $coorResult->num_rows === 0) {
    die("Coordinator not found for this user.");
}
$coor = $coorResult->fetch_assoc();
$coordinator_id = $coor['coordinator_id'];
$full_name = $coor['name'];
$email = $coor['email'];
$profile_picture = !empty($coor['profile_picture']) 
    ? '/ojtform/' . $coor['profile_picture'] 
    : '/ojtform/images/placeholder.jpg';


$coordinator_id = null;
$stmt = $conn->prepare("SELECT coordinator_id, name FROM coordinator WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$coor = $result->fetch_assoc(); 
$coordinator_id = $coor['coordinator_id'] ?? null;

if (!$coordinator_id) {
    die("Coordinator not found for this user.");
}

$sql = "SELECT t.*, u.email 
        FROM trainee t
        LEFT JOIN users u ON t.user_id = u.user_id
        WHERE t.coordinator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $coordinator_id);
$stmt->execute();
$result = $stmt->get_result();

$trainees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = ucwords(strtolower($row["first_name"] . ' ' . $row["surname"]));
        $fullAddress = $row["address"];

        if (preg_match('/(?:\b|^)([\w\s]+),?\s+([\w\s]+)$/', $fullAddress, $matches)) {
            $district = ucwords(strtolower(trim($matches[1]))); 
            $city = ucwords(strtolower(trim($matches[2])));
            $shortAddress = "$district, $city";
        } else {
            $shortAddress = ucwords(strtolower($fullAddress));
        }

        $trainees[] = [
            "trainee_id" => $row["trainee_id"], 
            "name" => $fullName,
            "email" => $row["email"],
            "phone" => $row["phone_number"],
            "address" => $shortAddress,
            "image" => !empty($row["profile_picture"]) ? "/ojtform/" . $row["profile_picture"] : "/ojtform/images/placeholder.jpg"
        ];
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports - OJT Attendance Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

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
      padding: 10px 16px;
      font-size: 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
      height: 55px;
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
  border: 2px solid #16a34a; 
  padding: 1.5rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: 14px;
  height: 130px;
   background-color: #ffffff;
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
  height: 100%;

  
}

/* Table container */
table {
  width: 100%;
  border-collapse: collapse;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #ffffff;
  border: 2px solid #16a34a;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  border-radius: 8px;
   border-radius: 8px;      
  overflow: hidden;  
  height: 100%;

}


thead {
  background-color:rgb(68, 131, 15);
  color: white;
  font-weight: bold;

  
}



th, td {
  padding: 12px 16px;
  text-align: left;
  font-size: 14px;
  
}

tbody td {
  line-height: 1.4;
  white-space: nowrap;  
  height: 50px;         
  vertical-align: middle;
}


tbody tr {
  border-bottom: 1px solid #d1d5db; 
}


tbody tr:hover {
  background-color: #f0fdf4;
}


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

.modal-overlay {
  display: none; 
  position: fixed;
  top: 0; 
  left: 0;
  width: 100%; 
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: 9999;
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.2s ease-in-out;
}

 .main {
      flex: 1;
      padding: 32px;
      overflow-y: auto;
      margin-left: 20px;
      margin-right: 20px;
      margin-top: 10px;
    }
    .trainee-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    @keyframes fadeSlideIn {
  0% {
    opacity: 0;
    transform: translateY(40px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.trainee-box {
  background-color: white;
  border: 2px solid #166534;
  border-radius: 12px;
  padding-top: 16px;
  padding-bottom: 16px;
  margin: 0px;
  text-align: center;

  opacity: 0;
  animation: fadeSlideIn 0.6s ease-out forwards;
  animation-fill-mode: forwards;

  transform: translateY(40px);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  will-change: transform, box-shadow;
}

.trainee-box:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
  z-index: 10;
}

    .trainee-box:nth-child(1) { animation-delay: 0.1s; }
.trainee-box:nth-child(2) { animation-delay: 0.2s; }
.trainee-box:nth-child(3) { animation-delay: 0.3s; }
.trainee-box:nth-child(4) { animation-delay: 0.4s; }
.trainee-box:nth-child(5) { animation-delay: 0.5s; }
.trainee-box:nth-child(6) { animation-delay: 0.6s; }

    .trainee-img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      margin-bottom: 12px;
      object-fit: cover;
    }
    .trainee-name {
      margin-bottom: 6px;
      color: #166534;
      line-height: 5px;
    }
    .trainee-email {
      margin-bottom: 12px;
      font-size: 14px;
    }
    .trainee-btn {
      background-color: #166534;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 15px;
      margin-bottom: 10px;
      cursor: pointer;
    }
    .trainee-contact {
      font-size: 16px;
      color: #444;
      margin-top: 20px;
    }


.modal-box {
  background-color: #fff;
  padding: 24px;
  border-radius: 16px;
  width: 420px;
  max-width: 90%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  animation: slideDown 0.25s ease-out;
}


.modal-title {
  font-size: 1.5rem;
  color: #14532d;
  margin-bottom: 4px;
}

.modal-subtitle {
  color: #4b5563;
  font-size: 14px;
  margin-bottom: 16px;
}

.highlighted-name {
  font-weight: 600;
  color: #15803d;
}

.dataTables_wrapper {
  font-size: 14px;
}
.dataTables_filter {
  float: right;
  margin-bottom: 10px;
}



.remarks-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
  font-size: 14px;
  outline-color: #16a34a;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}


.modal-actions {
  margin-top: 18px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-submit {
  background-color: #16a34a;
  color: white;
  border: none;
  padding: 10px 18px;
  font-weight: bold;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-submit:hover {
  background-color: #15803d;
}

.btn-cancel {
  background-color: #e5e7eb;
  color: #374151;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-cancel:hover {
  background-color: #d1d5db;
}


@keyframes slideDown {
  from {
    transform: translateY(-15px);
    opacity: 0;
  }
  to {
    transform: translateY(0px);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    background-color: rgba(0, 0, 0, 0);
  }
  to {
    background-color: rgba(0, 0, 0, 0.4);
  }
}


  @media print {
    .back-department {
      display: none;
    }
  }

.modalh2{
    font-size: 1.5rem;
    color: #14532d;
    margin-bottom: 12px;
    margin-left:6px;
}

.modal-overlay {
  position: fixed;
  top: 0; 
  left: 0;
  width: 100%; 
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  z-index: 9999;
  display: flex;
  justify-content: center;
  align-items: center;
  animation: fadeIn 0.2s ease-in-out;
}

.modal-content {
  background: #ffffff;
  width: 80%;
  max-height: 85%;
  overflow-y: auto;
  border-radius: 16px;
  padding: 24px 32px;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
  animation: slideDown 0.25s ease-out;
}

.modal-title {
  font-size: 1.6rem;
  color: #14532d;
  font-weight: 600;
  text-align: center;
  margin-bottom: 20px;
}

.modal-table-container {
  overflow-x: auto;
}

.modal-table {
  width: 100%;
  border-collapse: collapse;
  border: 1px solid #ccc;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  font-size: 14px;
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}

.modal-table thead {
  background-color: #166534;
  color: white;
  font-weight: bold;
}

.modal-table th,
.modal-table td {
  padding: 12px 16px;
  text-align: center;
  border-bottom: 1px solid #e2e8f0;
}

.modal-table tbody tr:hover {
  background-color: #f0fdf4;
}

@keyframes slideDown {
  from {
    transform: translateY(-10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}


  </style>
</head>
<body>

<div class="container">
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
      <a href="coorddashboard.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M3 9L12 2l9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        DASHBOARD
      </a>
    </li>
    <li>
      <a href="dtrmonitoring.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 4h16v16H4z"/>
          <line x1="8" y1="2" x2="8" y2="22"/>
          <line x1="16" y1="2" x2="16" y2="22"/>
        </svg>
        DTR MONITORING
      </a>
    </li>
    <li>
      <a href="coordupdate.php">
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
      <a href="coordblog.php">
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
 <a href="/ojtform/logout.php">
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
  <div class="content">
<!-- Topbar -->

<div class="topbar" style="
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 18px;
    border: none;
">

    <!-- Department Name -->
    <span>Daily Time Record Monitoring</span>

</div>


   <main class="main">
      <?php if (empty($trainees)): ?>
  <div style="text-align:center; font-size: 1.2rem; color: #888; padding: 40px;">
    No trainees assigned to you yet.
  </div>
<?php else: ?>
  <div class="trainee-grid">
    <?php foreach ($trainees as $id => $trainee): ?>
      <div class="trainee-box" data-name="<?= strtolower($trainee['name']) ?>">
        <img src="<?= htmlspecialchars($trainee['image']) ?>" alt="Profile" class="trainee-img">
        <h3 class="trainee-name"><?= htmlspecialchars($trainee['name']) ?></h3>
        <p class="trainee-email"><?= htmlspecialchars($trainee['email']) ?></p>
        <button class="trainee-btn" data-trainee-id="<?= $trainee['trainee_id'] ?>">View DTR</button>
        <p class="trainee-contact"><?= htmlspecialchars($trainee['phone']) ?> | <?= htmlspecialchars($trainee['address']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
    <!-- DTR Modal -->
<div id="dtrModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <h2 id="modalTitle" class="modal-title">Trainee DTR</h2>
    <div class="modal-table-container">

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
  <div id="recordCount" style="font-weight: bold;"></div>
  <button onclick="downloadCSV()" class="trainee-btn">
    <i class="bi bi-download"></i> Download CSV
  </button>
</div>


      <table id="dtrTable" class="modal-table display" style="width: 100%;">

        <thead>
          <tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Total Hours</th>
          </tr>
        </thead>
        <tbody id="dtrBody">
          <!-- Data will be injected here -->
        </tbody>
      </table>
    </div>
    <div style="text-align: center; margin-top: 20px;">
      <button class="trainee-btn" onclick="closeDTRModal()">Close</button>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="/ojtform/autologout.js"></script>

<script>
function closeDTRModal() {
  document.getElementById('dtrModal').style.display = 'none';
  document.getElementById('dtrBody').innerHTML = '';
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.trainee-btn').forEach(button => {
    button.addEventListener('click', () => {
      const traineeId = button.getAttribute('data-trainee-id');
      const traineeName = button.closest('.trainee-box').querySelector('.trainee-name').textContent;
      document.getElementById('modalTitle').textContent = traineeName + "'s Daily Time Record (DTR)";

      fetch('?fetch_dtr=1&trainee_id=' + traineeId)
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    dtrData = data;

   
    if ($.fn.DataTable.isDataTable('#dtrTable')) {
      $('#dtrTable').DataTable().clear().destroy();
    }

    const tbody = document.getElementById('dtrBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
      
      tbody.innerHTML = '<tr><td colspan="4" class="text-center">No attendance records found.</td></tr>';

   
    } else {
    
      data.forEach(record => {
        const row = `<tr>
          <td>${record.date}</td>
          <td>${record.time_in}</td>
          <td>${record.time_out}</td>
          <td>${record.total_hours}</td>
        </tr>`;
        tbody.innerHTML += row;
      });

     
      $('#dtrTable').DataTable({
        paging: true,
        pageLength: 5,
        ordering: true,
        order: [[0, 'desc']],
        searching: true
      });
    }

    document.getElementById('recordCount').textContent = `Total Records: ${dtrData.length}`;
    document.getElementById('dtrModal').style.display = 'flex';
  })


        .catch(error => {
          console.error('Fetch Error:', error);
        });
    });
  });
});

let dtrData = [];
let currentPage = 1;
const rowsPerPage = 5;

function downloadCSV() {
  if (dtrData.length === 0) return;

  const headers = ['Date', 'Time In', 'Time Out', 'Total Hours'];
  const rows = dtrData.map(row => [row.date, row.time_in, row.time_out, row.total_hours]);
  let csvContent = 'data:text/csv;charset=utf-8,' 
                 + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "dtr_records.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>
