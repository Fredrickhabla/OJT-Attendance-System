<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$sys_user = $user['username'] ?? 'unknown_user';

require_once 'logger.php';
// Fetch current trainee info (if exists)
$stmt = $pdo->prepare("SELECT * FROM trainee WHERE user_id = ?");
$stmt->execute([$user_id]);
$trainee = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the associated coordinator info (if trainee exists and has coordinator)
$coordinator = [];
$coordinator_id = '';
if ($trainee && !empty($trainee['coordinator_id'])) {
    $coordinator_id = $trainee['coordinator_id'];
    $stmt = $pdo->prepare("SELECT * FROM coordinator WHERE coordinator_id = ?");
    $stmt->execute([$coordinator_id]);
    $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all coordinators for the dropdown
$stmt = $pdo->query("SELECT * FROM coordinator");
$all_coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all departments for dropdown
$stmt = $pdo->query("SELECT * FROM departments WHERE status = 'active'");
$all_departments = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Upload trainee photo
$traineePicturePath = null;
if (isset($_FILES['trainee_picture']) && $_FILES['trainee_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/trainees/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = uniqid('trainee_') . '_' . basename($_FILES['trainee_picture']['name']);
    $uploadPath = $uploadDir . $fileName;
    move_uploaded_file($_FILES['trainee_picture']['tmp_name'], $uploadPath);
    $traineePicturePath = $uploadPath;
}

// ✅ Keep the old one if not updated
if ($traineePicturePath === null && isset($trainee['profile_picture'])) {
    $traineePicturePath = $trainee['profile_picture'];
}

// Upload coordinator photo
$coordinatorPicturePath = null;
if (isset($_FILES['coordinator_picture']) && $_FILES['coordinator_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/coordinators/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileName = uniqid('coord_') . '_' . basename($_FILES['coordinator_picture']['name']);
    $uploadPath = $uploadDir . $fileName;
    move_uploaded_file($_FILES['coordinator_picture']['tmp_name'], $uploadPath);
    $coordinatorPicturePath = $uploadPath;
}

if ($coordinatorPicturePath === null && isset($coordinator['profile_picture'])) {
    $coordinatorPicturePath = $coordinator['profile_picture'];
}

// Set user name/email if from session or DB
$user_name = $trainee ? ($trainee['first_name'] . ' ' . $trainee['surname']) : '';
$user_email = $trainee['email'] ?? '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect user data
    $firstName = $_POST['firstName'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $school = $_POST['school'];
    $phoneNumber = $_POST['phoneNumber'];
    $address = $_POST['address'];
    $scheduleDays = $_POST['schedule_days'] ?? '';
    $scheduleStart = $_POST['schedule_start'] ?? '';
    $scheduleEnd = $_POST['schedule_end'] ?? '';
    $requiredHours = $_POST['requiredHours'] ?? 0;
    $departmentId = $_POST['department'] ?? null;

    $coordId = $_POST['existingCoordinator'];
    $coordinatorName = $_POST['coordName'];
    $coordinatorPosition = $_POST['position'];
    $coordinatorEmail = $_POST['coordEmail'];
    $coordinatorPhone = $_POST['phone'];



if (!empty($coordId) && $coordId !== $coordinator_id) {
 
} else {
  
    if (!empty($coordinator_id)) {
    // Save old data for audit
    $oldCoordinator = $coordinator;

    $stmt = $pdo->prepare("UPDATE coordinator SET name = ?, position = ?, email = ?, phone = ?, profile_picture = ? WHERE coordinator_id = ?");
    $stmt->execute([$coordinatorName, $coordinatorPosition, $coordinatorEmail, $coordinatorPhone, $coordinatorPicturePath, $coordinator_id]);
    $coordId = $coordinator_id;

    $oldCoordinator = $coordinator;
$newCoordinatorValues = [
    'name' => $coordinatorName,
    'position' => $coordinatorPosition,
    'email' => $coordinatorEmail,
    'phone' => $coordinatorPhone,
    'profile_picture' => $coordinatorPicturePath
];

$oldCoordValues = [];
$changedCoordValues = [];

foreach ($newCoordinatorValues as $key => $newVal) {
    $oldVal = $oldCoordinator[$key] ?? null;
    if ($oldVal != $newVal) {
        $changedCoordValues[$key] = $newVal;
        $oldCoordValues[$key] = $oldVal;
    }
}

if (!empty($changedCoordValues)) {
    logTransaction($pdo, $user_id, $user_name, "Updated Coordinator Info", $sys_user);
    logAudit($pdo, $user_id, "Update Coordinator", json_encode($changedCoordValues), json_encode($oldCoordValues), $sys_user);
}


} else {
    $coordId = uniqid('coord_');
    $stmt = $pdo->prepare("INSERT INTO coordinator (coordinator_id, name, position, email, phone, profile_picture) 
                        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$coordId, $coordinatorName, $coordinatorPosition, $coordinatorEmail, $coordinatorPhone, $coordinatorPicturePath]);

    logTransaction($pdo, $user_id, $user_name, "Created Coordinator", $sys_user);
    logAudit($pdo, $user_id, "Create Coordinator", json_encode([
        'name' => $coordinatorName,
        'position' => $coordinatorPosition,
        'email' => $coordinatorEmail,
        'phone' => $coordinatorPhone
    ]), null, $sys_user);
}

}

    if ($trainee) {
        
        $stmt = $pdo->prepare("UPDATE trainee SET 
        first_name = ?, 
        surname = ?, 
        email = ?, 
        school = ?, 
        phone_number = ?, 
        address = ?, 
        schedule_days = ?, 
        schedule_start = ?, 
        schedule_end = ?, 
        required_hours = ?, 
        profile_picture = ?, 
        coordinator_id = ?,
        department_id = ?
        WHERE user_id = ?");

    $stmt->execute([
        $firstName,
        $surname,
        $email,
        $school,
        $phoneNumber,
        $address,
        $scheduleDays,
        $scheduleStart,
        $scheduleEnd,
        $requiredHours,
        $traineePicturePath,
        $coordId,
        $departmentId,
        $user_id
    ]);
// Prepare new values
$newValues = [
    'first_name' => $firstName,
    'surname' => $surname,
    'email' => $email,
    'school' => $school,
    'phone_number' => $phoneNumber,
    'address' => $address,
    'schedule_days' => $scheduleDays,
    'schedule_start' => $scheduleStart,
    'schedule_end' => $scheduleEnd,
    'required_hours' => $requiredHours,
    'profile_picture' => $traineePicturePath,
    'coordinator_id' => $coordId,
    'department_id' => $departmentId
];

// Compare changes
$oldValues = [];
$changedValues = [];

foreach ($newValues as $key => $newVal) {
    $oldVal = $trainee[$key] ?? null;
    if ($oldVal != $newVal) {
        $changedValues[$key] = $newVal;
        $oldValues[$key] = $oldVal;
    }
}

// Log only if there are changes
if (!empty($changedValues)) {
    logTransaction($pdo, $user_id, $user_name, "Updated Trainee Profile", $sys_user);
    logAudit($pdo, $user_id, "Update Trainee", json_encode($changedValues), json_encode($oldValues), $sys_user);
}


    } else {
        $fullSchedule = $scheduleDays . ' (' . $scheduleStart . '-' . $scheduleEnd . ')';

$stmt = $pdo->prepare("UPDATE trainee SET 
      first_name = ?, 
    surname = ?, 
    email = ?, 
    school = ?, 
    phone_number = ?, 
    address = ?, 
    schedule_days = ?, 
    schedule_start = ?, 
    schedule_end = ?, 
    required_hours = ?, 
    profile_picture = ?, 
    coordinator_id = ?,
    department_id = ?
    WHERE user_id = ?");

$stmt->execute([
    $firstName,
    $surname,
    $email,
    $school,
    $phoneNumber,
    $address,
    $scheduleDays,
    $scheduleStart,
    $scheduleEnd,
    $requiredHours,
    $traineePicturePath,
    $coordId,
    $departmentId,
    $user_id
]);
    }

    echo "<script>alert('Trainee profile saved successfully!'); window.location.href='dashboardv2.php';</script>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile</title>
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
  overflow: hidden;
}

.dashboard {
  display: flex;
  height: 100vh;
  overflow: hidden;
}

/* Sidebar */
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
.main{
     flex: 1;
  overflow-y: auto;
  display: flex;
  justify-content: center;
  align-items: flex-start; /* change from center to align content from top */
  padding: 20px;
}
.content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      
    }

.topbar {
    display: flex;
  justify-content: flex-start;
  align-items: left;
  background-color: #14532d;
  color: white;
  padding: 16px;
  font-size: 20px;
  font-weight: bold;
  height: 60px;
  position: fixed;
  top: 0;
  width: 100%;

  z-index: 100;
}

  .container {
  display: flex;
  justify-content: center;
  align-items: flex-start; /* Align items to the top */
  padding: 2rem;
  width: 100%;
  margin-top: 70px; /* Adjust this value to match your .topbar height */
  
}



    .card {
      background-color: white;
      width: 100%;
  
   
      height: 100%;
      border-radius: 2rem;
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      max-height: 100vh;
      overflow-y: auto;
      position: relative;
      z-index: 1;
    }

    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .main-content{
        flex: 1;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;

    }

    @media (min-width: 768px) {
      .content {
        flex-direction: row;
      }
    }

    .left-section {
      flex: 1;
      padding: 1rem;
      overflow-y: auto;
      padding-top: 2rem; /* Adjust padding to account for the fixed topbar */
  
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      align-items: center;
      
       height: 100%;
    }

    @media (min-width: 768px) {
      .left-section {
        border-bottom: none;
        border-right: 1px solid #ddd;
      }
    }

    

    .middle-section,
    .right-section {
      padding: 1.4rem;
      flex: 1.2;
      overflow-y: auto;
      border-bottom: 1px solid #ddd;
      justify-content: space-between; /* Makes sure bottom content stays at the bottom */
  min-height: 100%; 
    }

    @media (min-width: 768px) {
      .middle-section {
        border-right: 1px solid #ddd;
      }
      .right-section {
        border-bottom: none;
        border-right: none;
      }
    }

    .avatar {
      width: 6rem;
      height: 6rem;
      background-color: #ccc;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }

    .avatar-small {
      width: 6rem;
      height: 6rem;
    }

    .avatar-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 50%;
    }

    .name {
      font-size: 1.4rem;
      font-weight: 600;
      margin: 0.5rem 0;
      line-height: 0.5;
    }

    .email {
      font-size: 0.95rem;
      color: #666;
      margin-bottom: 1.5rem;
      margin-top: 0px;
    }

    .btn {
      background-color: #1f8f59;
      color: white;
      border: none;
      padding: 0.4rem 1rem;
      border-radius: 9999px;
      cursor: pointer;
      transition: background-color 0.2s;
      font-size: 1rem;
    }

    .btn:hover {
      background-color: #166c45;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      flex-grow: 1;
    }

    .form-vertical {
      display: flex;
      flex-direction: column;
      gap: .7rem;
      margin-top: .5rem;
      flex-grow: 1;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      margin-top: 0px;
    }

    label {
  
      font-weight: 500;
    }

    input {
      padding: 0.4rem 0.8rem;
      border: 1px solid #1f8f59;
      border-radius: 9999px;
      font-size: 0.85rem;
      transition: border 0.2s;
      width: 100%;
    }

    input:focus {
      border-color: #166c45;
      outline: none;
    }

    .coordinator-avatar {
    display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;   /* Centers horizontally */

  gap: 1rem;
  margin-left: 6rem;
    }

    .actions {
      display: flex;
      justify-content: flex-end;
      margin-top: 1.2rem;
    }

    h2 {
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }

    #schedulePicker {
      display: none;
      position: absolute;
      background: #fff;
      border: 1px solid #1f8f59;
      border-radius: 0.5rem;
      padding: 0.5rem 0.75rem;
      margin-top: 0.3rem;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      z-index: 10;
      font-size: 0.9rem;
      max-width: 280px;
    }

    #schedulePicker .days {
      display: flex;
      gap: 0.4rem;
      user-select: none;
      flex-wrap: nowrap;
      overflow-x: auto;
    }

    #schedulePicker label {
      cursor: pointer;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      font-weight: 500;
    }

    #schedulePicker input[type="checkbox"] {
      vertical-align: middle;
      margin: 0;
      width: 16px;
      height: 16px;
      cursor: pointer;
    }

    #schedulePicker div:not(.days) {
      margin-top: 0.4rem;
    }

    #schedulePicker label.time-label {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      font-weight: 500;
      white-space: nowrap;
    }

    #schedulePicker input[type="time"] {
      border-radius: 9999px;
      border: 1px solid #1f8f59;
      padding: 0.2rem 0.4rem;
      font-size: 0.85rem;
      width: 90px;
      cursor: pointer;
    }

    #scheduleContainer {
      position: relative;
      max-width: 100%;
    }

    #schedule {
      cursor: pointer;
      border-radius: 9999px;
      padding: 0.4rem 0.9rem;
      border: 1px solid #1f8f59;
      font-size: 0.95rem;
      width: 100%;
    }

    .bg-image {
      position: absolute;
      bottom: -146px;
      left: 0;
      width: 410px;
      height: 460px;
      opacity: 0.3;
      z-index: 0;
      object-fit: cover;
      object-position: top right;
    }

    .profile-section h2{
      margin-bottom: 0;
    }

    /* Password Change Modal Styles */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.overlay-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.overlay-content h2 {
  margin-bottom: 20px;
  text-align: center;
}

.overlay-content .form-group {
  margin-bottom: 15px;
}

.overlay-content input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.overlay-content .form-actions {
  display: flex;
  justify-content: space-between;
}

.overlay-content .btn {
  padding: 8px 16px;
}

.overlay-content .cancel {
  background-color: #ccc;
  color: #333;
}
.passbtn {
    display: flex;
  justify-content: center; /* centers the whole checkbox-label in the section */
  margin-top: 200px;         /* pushes it to the bottom if container is flex-column */
  padding-top: 20px;
 

}



#passbtn:hover {
  background-color: #19794a;
  transform: translateY(-2px);
}

#passbtn:active {
  transform: scale(0.97);
}

.passbtn-container {
  display: flex;
  justify-content: center;
  margin-top: 5rem;

  }

  .big-select {
  font-size: 15px;
  padding: 2px;
  color: gray;
  width: 100%; /* Optional: full width */
  border-radius: 6px;
  border: 1px solid #1f8f59; /* <-- fixed this line */
}



.checkbox-label {
  display: flex;
  align-items: left;
  gap: 6px; /* Controls spacing between checkbox and text */
  font-size: 16px;
  cursor: pointer;
  white-space: nowrap;
  margin-top:35px;
}

.checkbox-label input[type="checkbox"] {
  margin: 0;     /* Removes default spacing */
  padding: 0;
  transform: scale(1.1); /* Optional: makes the checkbox slightly bigger */
}

.leftholder {
    width: 25%;
}
  </style>
  

</head>
<body>
<div class="dashboard">
  <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
        <img src="<?= !empty($trainee['profile_picture']) ? htmlspecialchars($trainee['profile_picture']) . '?v=' . time() : 'https://cdn-icons-png.flaticon.com/512/9131/9131529.png' ?>" alt="Profile" class="profile-pic" />
  <h2><?= htmlspecialchars($user_name) ?></h2>
  <p><?= htmlspecialchars($user_email) ?></p>
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
      <a href="attendance_formv2.php">
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
  <a href="logout.php">
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
    <div class="topbar">Attendance Form</div>
   
  <div class="container">
    <div class="card">
      <form method="POST" enctype="multipart/form-data" class="content">

        <!-- Left Section -->
         <div class="leftholder">
        <div class="left-section">
          <div class="avatar">
            <img id="trainee-preview" 
                 src="<?= !empty($trainee['profile_picture']) 
                        ? htmlspecialchars($trainee['profile_picture']) . '?v=' . time() 
                        : 'images/placeholder.jpg' ?>" 
                 alt="Trainee Photo" 
                 class="avatar-img">
          </div>
          <h2 class="name"><?= htmlspecialchars($user_name) ?></h2>
          <p class="email"><?= htmlspecialchars($user_email) ?></p>
          <label for="trainee_picture" class="btn"> Insert Photo</label>
          <input type="file" id="trainee_picture" name="trainee_picture" accept="image/*" style="display: none;">

          <div class="passbtn-container" style="margin-top: 16rem; ">
  <label class="checkbox-label">
    <input type="checkbox" id="togglePasswordForm">Change Password</label>
</div>
</div>

    

          
          
        </div>

        <!-- Middle Section -->
        <div class="middle-section">
          <h2>Profile Settings</h2>
          <div class="form-grid">
            <div class="form-group">
              <label for="firstName">First Name</label>
              <input id="firstName" type="text" name="firstName" value="<?= htmlspecialchars($trainee['first_name'] ?? '') ?>" required/>
            </div>
            <div class="form-group">
              <label for="surname">Surname</label>
              <input id="surname" type="text" name="surname" value="<?= htmlspecialchars($trainee['surname'] ?? '') ?>" required/>
            </div>
          </div>
          <div class="form-vertical">
            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" />
            </div>
            <div class="form-group">
              <label for="school">School</label>
              <input id="school" type="text" name="school" value="<?= htmlspecialchars($trainee['school'] ?? '') ?>" required/>
            </div>
            <div class="form-group">
              <label for="phoneNumber">Phone Number</label>
              <input id="phoneNumber" type="text" name="phoneNumber" value="<?= htmlspecialchars($trainee['phone_number'] ?? '') ?>" required/>
            </div>
            <div class="form-group">
              <label for="address">Address</label>
              <input id="address" type="text" name="address" value="<?= htmlspecialchars($trainee['address'] ?? '') ?>" required/>
            </div>

            <!-- Schedule -->
            <div class="form-group" id="scheduleContainer">
              <label for="schedule" style="font-size: 0.95rem;">Schedule</label>
              <input id="schedule" type="text" readonly placeholder="Click to select schedule" />
              <div id="schedulePicker">
                <div class="days">
                  <label><input type="checkbox" name="days" value="M"> M</label>
                  <label><input type="checkbox" name="days" value="T"> T</label>
                  <label><input type="checkbox" name="days" value="W"> W</label>
                  <label><input type="checkbox" name="days" value="Th"> Th</label>
                  <label><input type="checkbox" name="days" value="F"> F</label>
                </div>
              <input type="hidden" name="schedule_days" id="schedule_days" value="<?= htmlspecialchars($trainee['schedule_days'] ?? '') ?>" />
<input type="hidden" name="schedule_start" id="schedule_start" value="<?= htmlspecialchars($trainee['schedule_start'] ?? '') ?>" />
<input type="hidden" name="schedule_end" id="schedule_end" value="<?= htmlspecialchars($trainee['schedule_end'] ?? '') ?>" />

                <div>
                  <label class="time-label" for="startTime">Start:
                    <input id="startTime" type="time" value="14:30" />
                  </label>
                </div>
                <div>
                  <label class="time-label" for="endTime">End:
                    <input id="endTime" type="time" value="13:00" />
                  </label>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="requiredHours">Required Hours</label>
              <input id="requiredHours" type="number" name="requiredHours" value="<?= htmlspecialchars($trainee['required_hours']?? '') ?>" />
            </div>

            <div class="form-group">
  <label for="department">Department</label>
  <select id="department" name="department" class = "big-select" required>
    <option value="">-- Choose Department --</option>
    <?php foreach ($all_departments as $dept): ?>
      <option value="<?= htmlspecialchars($dept['department_id']) ?>" 
        <?= (isset($trainee['department_id']) && $trainee['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($dept['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

          </div>

        </div>

        <!-- Right Section -->
        <div class="right-section">
          <h2>Coordinator Profile</h2>
          <div class="coordinator-avatar">
            <div class="avatar avatar-small">
              <img id="coordinator-preview" 
                   src="<?= !empty($coordinator['profile_picture']) 
                          ? htmlspecialchars($coordinator['profile_picture']) . '?v=' . time() 
                          : 'images/placeholder.jpg' ?>" 
                   alt="Coordinator Photo" 
                   class="avatar-img">
            </div>
            <label for="coordinator_picture" class="btn"> Insert Photo</label>
            <input type="file" id="coordinator_picture" name="coordinator_picture" accept="image/*" style="display: none;">
          </div>

          <div class="form-vertical">
            <div class="form-group">
              <label for="existingCoordinator">Select Existing Coordinator (optional)</label>
              <select id="existingCoordinator" name="existingCoordinator">
                <option value="">-- Choose a Coordinator --</option>
                <?php foreach ($all_coordinators as $coord): ?>
                  <option value="<?= $coord['coordinator_id'] ?>" <?= ($coord['coordinator_id'] == $coordinator_id ? 'selected' : '') ?>>
                    <?= htmlspecialchars($coord['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="coordName">Name</label>
              <input id="coordName" type="text" name="coordName" value="<?= htmlspecialchars($coordinator['name'] ?? '') ?>" required/>
            </div>
            <div class="form-group">
              <label for="position">Position</label>
              <input id="position" type="text" name="position" value="<?= htmlspecialchars($coordinator['position'] ?? '') ?>" required/>
            </div>
            <div class="form-group">
              <label for="coordEmail">Email</label>
              <input id="coordEmail" type="email" name="coordEmail" value="<?= htmlspecialchars($coordinator['email'] ?? '') ?>" required/>
            </div>
            <div class="form-group">
              <label for="phone">Phone</label>
              <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($coordinator['phone'] ?? '') ?>" required/>
            </div>
          </div>

          <div class="actions">
            <button class="btn" type="submit" id="saveBtn">Save</button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

      </div>
    </div>

    <!-- Overlay Change Password Form -->
<div id="passwordOverlay" class="overlay" style="display: none;">
  <div class="overlay-content">
    <h2>Change Password</h2>
    <form id="passwordForm">
      <div class="form-group">
        <label for="currentPassword">Current Password</label>
        <input type="password" id="currentPassword" name="currentPassword" required>
      </div>
      <div class="form-group">
        <label for="newPassword">New Password</label>
        <input type="password" id="newPassword" name="newPassword" required>
      </div>
      <div class="form-group">
        <label for="confirmPassword">Confirm New Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>
      </div>
      <div class="form-actions">
        <button type="button" class="btn" onclick="submitPasswordChange()">Update</button>
        <button type="button" class="btn cancel" onclick="closePasswordForm()">Cancel</button>
      </div>
    </form>
  </div>
</div>


      <script>
  const scheduleInput = document.getElementById('schedule');
  const schedulePicker = document.getElementById('schedulePicker');
  const dayCheckboxes = schedulePicker.querySelectorAll('input[name="days"]');
  const startTimeInput = document.getElementById('startTime');
  const endTimeInput = document.getElementById('endTime');
  const scheduleDaysInput = document.getElementById('schedule_days');
  const scheduleStartInput = document.getElementById('schedule_start');
  const scheduleEndInput = document.getElementById('schedule_end');

  function formatTime24to12(time24) {
    if (!time24) return '';
    let [h, m] = time24.split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${m.toString().padStart(2,'0')} ${ampm}`;
  }

  function updateScheduleInput() {
    const selectedDays = Array.from(dayCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value);

    if (!selectedDays.length || !startTimeInput.value || !endTimeInput.value) {
      scheduleInput.value = '';
      return;
    }

    const start = formatTime24to12(startTimeInput.value);
    const end = formatTime24to12(endTimeInput.value);

    scheduleInput.value = `${selectedDays.join(', ')} ${start}–${end}`;
  }

  function syncScheduleToHiddenInputs() {
    const selectedDays = Array.from(dayCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value)
      .join(',');

    scheduleDaysInput.value = selectedDays;
    scheduleStartInput.value = startTimeInput.value;
    scheduleEndInput.value = endTimeInput.value;
  }

  // Toggle picker visibility
  scheduleInput.addEventListener('click', () => {
    schedulePicker.style.display = schedulePicker.style.display === 'block' ? 'none' : 'block';
  });

  // Hide picker when clicking outside
  document.addEventListener('click', e => {
    if (!scheduleInput.contains(e.target) && !schedulePicker.contains(e.target)) {
      schedulePicker.style.display = 'none';
    }
  });

  // Update display on any change
  dayCheckboxes.forEach(cb => cb.addEventListener('change', updateScheduleInput));
  startTimeInput.addEventListener('input', updateScheduleInput);
  endTimeInput.addEventListener('input', updateScheduleInput);

  // Sync hidden fields on form submit
  document.querySelector('form').addEventListener('submit', syncScheduleToHiddenInputs);

  // Initial display update
  updateScheduleInput();

  // Preview trainee photo
  document.getElementById('trainee_picture').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('trainee-preview');
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  // Preview coordinator photo
  document.getElementById('coordinator_picture').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('coordinator-preview');
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
  const savedDays = scheduleDaysInput.value.split(',');
  const savedStart = scheduleStartInput.value;
  const savedEnd = scheduleEndInput.value;

  // Set checkboxes
  dayCheckboxes.forEach(cb => {
    if (savedDays.includes(cb.value)) {
      cb.checked = true;
    }
  });

  // Set times
  if (savedStart) startTimeInput.value = savedStart;
  if (savedEnd) endTimeInput.value = savedEnd;

  updateScheduleInput();
});

// Show/Hide Overlay using checkbox
const togglePasswordCheckbox = document.getElementById("togglePasswordForm");

togglePasswordCheckbox.addEventListener("change", () => {
  const overlay = document.getElementById("passwordOverlay");
  overlay.style.display = togglePasswordCheckbox.checked ? "flex" : "none";
});

// Close password form (and uncheck checkbox)
function closePasswordForm() {
  document.getElementById("passwordOverlay").style.display = "none";
  document.getElementById("togglePasswordForm").checked = false;
}

// Handle Password Submission
function submitPasswordChange() {
  const current = document.getElementById("currentPassword").value;
  const newPass = document.getElementById("newPassword").value;
  const confirm = document.getElementById("confirmPassword").value;

  if (!current || !newPass || !confirm) {
    alert("All fields are required.");
    return;
  }

  if (newPass !== confirm) {
    alert("New passwords do not match.");
    return;
  }

  // Send to PHP
  fetch("change_password.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `current=${encodeURIComponent(current)}&new=${encodeURIComponent(newPass)}`
  })
  .then(res => res.text())
  .then(msg => {
    alert(msg);
    if (msg.toLowerCase().includes("success")) {
      closePasswordForm();
    }
  })
  .catch(() => {
    alert("Something went wrong.");
  });
}




</script>
