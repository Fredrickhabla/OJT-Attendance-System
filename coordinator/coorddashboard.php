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


$totalTrainees = 0;
$completed = 0;
$ongoing = 0;

$traineeData = [];

$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$countResult = $conn->query("SELECT COUNT(*) AS total FROM trainee WHERE coordinator_id = '$coordinator_id'");
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

$traineeResult = $conn->query("SELECT * FROM trainee WHERE coordinator_id = '$coordinator_id' LIMIT $perPage OFFSET $offset");

if ($traineeResult) {
    while ($trainee = $traineeResult->fetch_assoc()) {
        $trainee_id = $trainee['trainee_id'];
        $required = $trainee['required_hours'];

        // Get completed hours
        $attendanceQuery = $conn->query("SELECT SUM(hours) AS total_hours FROM attendance_record WHERE trainee_id = '$trainee_id'");
        $attendance = $attendanceQuery->fetch_assoc();
        $completed_hours = floatval($attendance['total_hours'] ?? 0);

     
        $status = ($completed_hours >= $required) ? 'Completed' : 'Ongoing';

        if ($status === 'Completed') {
            $completed++;
        } else {
            $ongoing++;
        }

        $totalTrainees++;

       
        $traineeData[] = [
            'trainee_id' => $trainee['trainee_id'],
            'name' => ucwords(strtolower($trainee['first_name'] . ' ' . $trainee['surname'])),
            'school' => $trainee['school'],
            'required' => $required,
            'completed' => $completed_hours,
            'status' => $status,
            'remarks' => $trainee['remarks'] ?? ''
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
    <span>Trainee</span>

</div>


    <div class="main">

   <section class="cards">
  <div class="card">
    <!--  Trainee Icon -->
  
<svg xmlns="http://www.w3.org/2000/svg" height="60px" class="icon" viewBox="0 0 24 24" fill="#16a34a">
  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
</svg>

    <span class="card-label"><?= $totalTrainees ?> Trainee</span>
  </div>

  <div class="card">
   
    <svg xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="1 4 1 10 7 10" />
      <polyline points="23 20 23 14 17 14" />
      <path d="M20.49 9A9 9 0 0 0 5.51 5M3 14a9 9 0 0 0 15.49 4" />
    </svg>
    <span class="card-label"><?= $ongoing ?> Ongoing</span>
  </div>

  <div class="card">
   
    <svg xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 12l2 2l4 -4" />
      <circle cx="12" cy="12" r="10" />
    </svg>
    <span class="card-label"><?= $completed ?> Completed</span>
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
<tbody>
<?php
$rowCount = 0;
foreach ($traineeData as $trainee):
  $rowCount++;
?>
  <tr class="trainee-row" data-name="<?= htmlspecialchars($trainee['name']) ?>" data-id="<?= $trainee['trainee_id'] ?>">
  <td><?= htmlspecialchars($trainee['name']) ?></td>
  <td><?= htmlspecialchars($trainee['school']) ?></td>
  <td><?= $trainee['required'] ?></td>
  <td><?= $trainee['completed'] ?></td>
  <td><span class="badge <?= $trainee['status'] ?>"><?= $trainee['status'] ?></span></td>
  <td><?= htmlspecialchars($trainee['remarks']) ?></td>

</tr>

<?php endforeach; ?>

<?php
for ($i = $rowCount; $i < 8; $i++):
?>
  <tr>
    <td colspan="6" style="height: 50px; color: #aaa; text-align: center;">— Empty Slot —</td>
  </tr>
<?php endfor; ?>

</tbody>

  </table>

  <div style="padding: 1rem; text-align: center;">
  <?php if ($totalPages > 1): ?>
    <?php if ($page > 1): ?>
      <a href="?coordinator_id=<?= $coordinator_id ?>&page=<?= $page - 1 ?>" style="margin-right: 10px;">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <?php if ($p == $page): ?>
        <strong><?= $p ?></strong>
      <?php else: ?>
        <a href="?coordinator_id=<?= $coordinator_id ?>&page=<?= $p ?>" style="margin: 0 5px;"><?= $p ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="?coordinator_id=<?= $coordinator_id ?>&page=<?= $page + 1 ?>" style="margin-left: 10px;">Next &raquo;</a>
    <?php endif; ?>
  <?php endif; ?>
</div>

</div>


<!-- Remarks Modal -->
<div id="remarksModal" class="modal-overlay">
  <div class="modal-box">
    <h2 class="modal-title">Add Remarks</h2>
    <p class="modal-subtitle">For: <span id="traineeName" class="highlighted-name"></span></p>

    <form action="save_remarks.php" method="POST">
      <input type="hidden" name="trainee_id" id="traineeId">
       <input type="hidden" name="coordinator_id" value="<?= htmlspecialchars($coordinator_id) ?>">

      
      <textarea 
        name="remarks" 
        placeholder="Type your remarks here..." 
        class="remarks-textarea" 
        required
      ></textarea>

      <div class="modal-actions">
        <button type="submit" class="btn-submit">Save</button>
        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>


<script>
function closeModal() {
  document.getElementById('remarksModal').style.display = 'none';
}

document.querySelectorAll('.trainee-row').forEach(row => {
  row.addEventListener('click', () => {
    const name = row.getAttribute('data-name');
    const id = row.getAttribute('data-id');

    document.getElementById('traineeName').innerText = name;
    document.getElementById('traineeId').value = id;
    document.getElementById('remarksModal').style.display = 'flex';
  });
});
</script>
<script src="/ojtform/autologout.js"></script>