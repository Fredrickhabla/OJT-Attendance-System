<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /ojtform/indexv2.php");
    exit();
}

require_once 'connection.php';
require_once 'logger.php';
$pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");

$user_id = $_SESSION['user_id'];
$user_name = $user_email = "";
$coordinator_id = null;

$stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $user_email);
$stmt->fetch();
$stmt->close();

$all_coordinators = [];
$result = $conn->query("SELECT coordinator_id, name FROM coordinator ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $all_coordinators[] = $row;
}

$departments = [];
$result = $conn->query("SELECT department_id, name FROM departments ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}


$trainee = [];
if (!empty($trainee_id)) {
    $stmt = $conn->prepare("SELECT first_name, surname, email, school, phone_number, address, schedule_days, schedule_start, schedule_end, required_hours, department_id, profile_picture FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("s", $trainee_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $surname, $email, $school, $phone_number, $address, $schedule_days, $schedule_start, $schedule_end, $required_hours, $department_id, $profile_picture);
    if ($stmt->fetch()) {
        $trainee = compact('first_name', 'surname', 'email', 'school', 'phone_number', 'address', 'schedule_days', 'schedule_start', 'schedule_end', 'required_hours', 'profile_picture');
    }
    $stmt->close();
}


$coordinator = [];
if (!empty($coordinator_id)) {
    $stmt = $conn->prepare("SELECT name, position, email, phone, profile_picture FROM coordinator WHERE coordinator_id = ?");
    $stmt->bind_param("s", $coordinator_id);
    $stmt->execute();
    $stmt->bind_result($name, $position, $coord_email, $coord_phone, $profile_picture);
    if ($stmt->fetch()) {
        $coordinator = compact('name', 'position', 'email', 'phone', 'profile_picture');
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $trainee_picture_path = null;
    if (!empty($_FILES['trainee_picture']['name'])) {
        $ext = pathinfo($_FILES['trainee_picture']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("trainee_") . "." . $ext;
        $trainee_picture_path = $uploadDir . $filename;
        move_uploaded_file($_FILES['trainee_picture']['tmp_name'], $trainee_picture_path);
    }

    $coordinator_picture_path = null;
    if (!empty($_FILES['coordinator_picture']['name'])) {
        $ext = pathinfo($_FILES['coordinator_picture']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("coord_") . "." . $ext;
        $coordinator_picture_path = $uploadDir . $filename;
        move_uploaded_file($_FILES['coordinator_picture']['tmp_name'], $coordinator_picture_path);
    }


    $first_name = trim($_POST['firstName'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $school = trim($_POST['school'] ?? '');
    $phone_number = trim($_POST['phoneNumber'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $required_hours = (int)($_POST['requiredHours'] ?? 0);
    $schedule_days = $_POST['schedule_days'] ?? '';
    $schedule_start = $_POST['schedule_start'] ?? '';
    $schedule_end = $_POST['schedule_end'] ?? '';
    $department_id = $_POST['department_id'] ?? null;

    $selectedCoordinatorId = $_POST['existingCoordinator'] ?? '';
    $coord_name = trim($_POST['coordName'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $coord_email = trim($_POST['coordEmail'] ?? '');
    $coord_phone = trim($_POST['phone'] ?? '');

    $updated_user_name = "$first_name $surname";
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("sss", $updated_user_name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

 
    $stmt = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($trainee_id_check);
    $hasTrainee = $stmt->fetch();
    $stmt->close();

    if ($hasTrainee) {

    $stmt = $conn->prepare("SELECT first_name, surname, email, school, phone_number, address FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("s", $trainee_id);
    $stmt->execute();
    $stmt->bind_result($old_fname, $old_sname, $old_email, $old_school, $old_phone, $old_address);
    $stmt->fetch();
    $stmt->close();
}


  

        if ($hasTrainee) {
   
    $stmt = $conn->prepare("SELECT first_name, surname, email, school, phone_number, address FROM trainee WHERE trainee_id = ?");
    $stmt->bind_param("s", $trainee_id);
    $stmt->execute();
    $stmt->bind_result($old_fname, $old_sname, $old_email, $old_school, $old_phone, $old_address);
    $stmt->fetch();
    $stmt->close();
}

    if (!$hasTrainee) {
        $trainee_id = uniqid("trainee_");
        $stmt = $conn->prepare("INSERT INTO trainee (trainee_id, user_id, first_name, surname, email, school, phone_number, address, schedule_days, schedule_start, schedule_end, required_hours, department_id, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssiss", $trainee_id, $user_id, $first_name, $surname, $email, $school, $phone_number, $address, $schedule_days, $schedule_start, $schedule_end, $required_hours, $department_id, $trainee_picture_path);

        $stmt->execute();
        $stmt->close();
    } else {
        $trainee_id = $trainee_id_check;
        if ($trainee_picture_path) {
            $stmt = $conn->prepare("UPDATE trainee SET first_name=?, surname=?, email=?, school=?, phone_number=?, address=?, schedule_days=?, schedule_start=?, schedule_end=?, required_hours=?, department_id=?, profile_picture=? WHERE trainee_id=?");
            $stmt->bind_param("sssssssssssss", $first_name, $surname, $email, $school, $phone_number, $address, $schedule_days, $schedule_start, $schedule_end, $required_hours, $department_id, $trainee_picture_path, $trainee_id);
        } else {
            $stmt = $conn->prepare("UPDATE trainee SET first_name=?, surname=?, email=?, school=?, phone_number=?, address=?, schedule_days=?, schedule_start=?, schedule_end=?, required_hours=?, department_id=?WHERE trainee_id=?");
            $stmt->bind_param("sssssssssiss", $first_name, $surname, $email, $school, $phone_number, $address, $schedule_days, $schedule_start, $schedule_end, $required_hours, $department_id, $trainee_id);
        }
        $stmt->execute();
        $stmt->close();
    }


logAudit(
    $pdo,
    $user_id,
    "New trainee profile",
    json_encode([
        "first_name" => $first_name,
        "surname" => $surname,
        "email" => $email,
        "school" => $school,
        "phone" => $phone_number,
        "address" => $address
    ]),
    "-",
    $first_name 
);



if (!empty($selectedCoordinatorId)) {
  
    $stmt = $conn->prepare("UPDATE trainee SET coordinator_id = ? WHERE trainee_id = ?");
    $stmt->bind_param("ss", $selectedCoordinatorId, $trainee_id);
    $stmt->execute();
    $stmt->close();

} else {
  
    $coordinator_id = uniqid("coord_");

    $stmt = $conn->prepare("INSERT INTO coordinator (coordinator_id, name, position, email, phone, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $coordinator_id, $coord_name, $position, $coord_email, $coord_phone, $coordinator_picture_path);
    $stmt->execute();
    $stmt->close();

   
    $stmt = $conn->prepare("UPDATE trainee SET coordinator_id = ? WHERE trainee_id = ?");
    $stmt->bind_param("ss", $coordinator_id, $trainee_id);
    $stmt->execute();
    $stmt->close();

logTransaction($pdo, $user_id, $updated_user_name, "Added new coordinator", $first_name);
logAudit(
    $pdo,
    $user_id,
    "New coordinator added",
    json_encode([
        "name" => $coord_name,
        "position" => $position,
        "email" => $coord_email,
        "phone" => $coord_phone
    ]),
    "-",
    $first_name
);
}

if ($trainee_picture_path) {
    logTransaction($pdo, $user_id, $user_name, "Uploaded new trainee photo", $first_name);
}

if ($coordinator_picture_path) {
    logTransaction($pdo, $user_id, $user_name, "Uploaded new coordinator photo", $first_name);
}




logTransaction($pdo, $user_id, $updated_user_name, "Profile updated", $first_name);

echo "<script>
    alert('Profile saved successfully.');
    window.location.href = 'dashboardv2.php';
</script>";
exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>OJT Acer Profile</title>
  <style>
    * {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: "Segoe UI", sans-serif;
  background-color: white;
  color: #333;
  height: 100vh;
}

.container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  padding: 2rem;
}

.card {
  background-color: white;
  width: 100%;
  max-width: 100%px;
  height: auto;
  border-radius: 2rem;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  max-height: 100vh;
  overflow-y: auto;
   position: relative;
  z-index: 1;
  margin: 20px;
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
}

@media (min-width: 768px) {
  .content {
    flex-direction: row;
  }
}


.left-section {
  flex: 0.7;
  padding: 2rem;
  overflow-y: auto;
    position: relative;
    overflow: hidden;
}

.middle-section{
    flex: 1.2;
  padding: 2rem;
  overflow-y: auto;
}
.right-section {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

.left-section {
  border-bottom: 1px solid #ddd;
  display: flex;
  flex-direction: column;
  align-items: center;
  
}

@media (min-width: 768px) {
  .left-section {
    border-bottom: none;
    border-right: 1px solid #ddd;
  }
}

.middle-section,
.right-section {
  border-bottom: 1px solid #ddd;
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
  width: 9rem;
  height: 9rem;
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

.icon {
  width: 4rem;
  height: 4rem;
  color: #888;
}

.icon-small {
  width: 3rem;
  height: 3rem;
  color: #888;
}

.name {
  font-size: 2rem;
  font-weight: 600;
  margin: 0.5rem 0;
  line-height: 0.5;
}

.email {
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 1.5rem;
  margin-top: 0px;
}

.btn {
  background-color: #1f8f59;
  color: white;
  border: none;
  padding: 0.6rem 1.4rem;
  border-radius: 9999px;
  cursor: pointer;
  transition: background-color 0.2s;
  font-size: 1.0rem;
}

.btn:hover {
  background-color: #166c45;
}

.btn.small {
  padding: 0.4rem 1rem;
  font-size: 0.85rem;
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
  gap: 1rem;
  margin-top: 1rem;s
  flex-grow: 1;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 0.25rem;
  font-weight: 500;
}

input {
  padding: 0.5rem 1rem;
  border: 1px solid #1f8f59;
  border-radius: 9999px;
  font-size: 0.95rem;
  transition: border 0.2s;
width: 100%;
}

input:focus {
  border-color: #166c45;
  outline: none;
}

.coordinator-avatar {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.5rem;
}

.actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 2.2rem;
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
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
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
    box-sizing: border-box;
    width: 90px; 
    cursor: pointer;
  }
  #scheduleContainer {
    position: relative;
    max-width: 100%;
    position: relative; 
  }
  #schedule {
    cursor: pointer;
    border-radius: 9999px;
    padding: 0.4rem 0.9rem;
    border: 1px solid #1f8f59;
    font-size: 0.95rem;
    width: 100%;
    box-sizing: border-box;
  }

  .bg-image {
 position: absolute;
  bottom: -150px;;
  left: 0;  
  width: 410px; 
  height:460px; 
  opacity: 0.3; 
  z-index: 0; 
  object-fit: cover; 
  object-position: top right; 
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.dept {
  margin-top: 1rem;
}

.big-select {
  font-size: 12px;
  padding: 10px;
  color: gray;
  width: 100%; 
  border-radius: 6px;
  border: 1px solid #1f8f59; 
}



  </style>
</head>
<body>
  <div class="container">
    <div class="card">
       <form method="POST" enctype="multipart/form-data" class="content">
        

        <!-- Left Section -->
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
          <img src="images/ojtlogo.png" alt="Background" class="bg-image" />

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

    <input type="hidden" name="schedule_days" id="schedule_days" />
<input type="hidden" name="schedule_start" id="schedule_start" />
<input type="hidden" name="schedule_end" id="schedule_end" />
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
          </div>
          <div class="form-group">
  <label for="department_id" class="dept">Department</label>
  <select id="department_id" name="department_id" class="big-select" required>
    <option value="" class="deptlist">-- Select Department --</option>
    <?php foreach ($departments as $dept): ?>
      <option value="<?= $dept['department_id'] ?>" 
        <?= (isset($trainee['department_id']) && $trainee['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($dept['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
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
  

  
</body>
<script>

    const scheduleDaysInput = document.getElementById('schedule_days');
const scheduleStartInput = document.getElementById('schedule_start');
const scheduleEndInput = document.getElementById('schedule_end');

function syncScheduleToHiddenInputs() {
  const selectedDays = Array.from(dayCheckboxes)
    .filter(cb => cb.checked)
    .map(cb => cb.value)
    .join(',');

  scheduleDaysInput.value = selectedDays;
  scheduleStartInput.value = startTimeInput.value;
  scheduleEndInput.value = endTimeInput.value;
}

document.querySelector('form').addEventListener('submit', syncScheduleToHiddenInputs);


 const scheduleInput = document.getElementById('schedule');
  const schedulePicker = document.getElementById('schedulePicker');
  const dayCheckboxes = schedulePicker.querySelectorAll('input[name="days"]');
  const startTimeInput = document.getElementById('startTime');
  const endTimeInput = document.getElementById('endTime');

  scheduleInput.addEventListener('click', () => {
    schedulePicker.style.display = schedulePicker.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', e => {
    if (!scheduleInput.contains(e.target) && !schedulePicker.contains(e.target)) {
      schedulePicker.style.display = 'none';
    }
  });

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

  dayCheckboxes.forEach(cb => cb.addEventListener('change', updateScheduleInput));
  startTimeInput.addEventListener('input', updateScheduleInput);
  endTimeInput.addEventListener('input', updateScheduleInput);

  updateScheduleInput();

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

document.getElementById('coordinator_picture').addEventListener('change', function (event) {
    const preview = document.getElementById('coordinator-preview');
    const file = event.target.files[0];
    if (file) {
      preview.src = URL.createObjectURL(file);
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    const coordinatorSelect = document.getElementById("existingCoordinator");
    const coordInputs = [
        document.getElementById("coordName"),
        document.getElementById("position"),
        document.getElementById("coordEmail"),
        document.getElementById("phone"),
        document.getElementById("coordinator_picture")
    ];

    function toggleCoordinatorInputs() {
        const isExistingSelected = coordinatorSelect.value !== "";

        coordInputs.forEach(input => {
            input.readOnly = isExistingSelected;
            input.disabled = isExistingSelected;

            
            if (isExistingSelected) {
                input.title = "You can't edit an existing user coordinator";
            } else {
                input.title = "Fill in this field if you don't select an existing coordinator";
            }
        });
    }

    toggleCoordinatorInputs(); 
    coordinatorSelect.addEventListener("change", toggleCoordinatorInputs);
});


  
</script>
</html>


