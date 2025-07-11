    logger.php

<?php
function generateUUID() {
    return uniqid("log_", true); // Or use better UUID if needed
}

// Transaction Log
function logTransaction($pdo, $user_id, $fullname, $description, $transaction_user) {
    $transaction_id = generateUUID();
    $stmt = $pdo->prepare("INSERT INTO transaction_logs (
        transaction_id, user_id, fullname, transaction_date, transaction_description, transaction_user
    ) VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->execute([$transaction_id, $user_id, $fullname, $description, $transaction_user]);
}

// Audit Log
function logAudit($pdo, $user_id, $activity, $new_value, $old_value, $sys_user, $success_yn = 'Y') {
    $audit_id = generateUUID();
    $stmt = $pdo->prepare("INSERT INTO audit_logs (
        audit_id, user_id, log_date, activity, new_value, old_value, sys_user, success_yn
    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
    $stmt->execute([$audit_id, $user_id, $activity, $new_value, $old_value, $sys_user, $success_yn]);
}
?>



---------------------
profile.php

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /ojtform/indexv2.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// Fetch trainee data (if $trainee_id is defined somewhere else)
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

// Fetch coordinator data (if $coordinator_id is defined)
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
    // Upload pictures
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

    // Collect POST data
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

    // Check if trainee already exists
    $stmt = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($trainee_id_check);
    $hasTrainee = $stmt->fetch();
    $stmt->close();

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

    // Coordinator logic
if (!empty($selectedCoordinatorId)) {
    // ✅ Use existing coordinator — update trainee with selected coordinator_id
    $stmt = $conn->prepare("UPDATE trainee SET coordinator_id = ? WHERE trainee_id = ?");
    $stmt->bind_param("ss", $selectedCoordinatorId, $trainee_id);
    $stmt->execute();
    $stmt->close();

} else {
    // ✅ No coordinator selected — create new coordinator
    $coordinator_id = uniqid("coord_");

    $stmt = $conn->prepare("INSERT INTO coordinator (coordinator_id, name, position, email, phone, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $coordinator_id, $coord_name, $position, $coord_email, $coord_phone, $coordinator_picture_path);
    $stmt->execute();
    $stmt->close();

    // ✅ Update trainee with new coordinator_id
    $stmt = $conn->prepare("UPDATE trainee SET coordinator_id = ? WHERE trainee_id = ?");
    $stmt->bind_param("ss", $coordinator_id, $trainee_id);
    $stmt->execute();
    $stmt->close();
}

// Final message and redirect
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
    return ${h}:${m.toString().padStart(2,'0')} ${ampm};
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

    scheduleInput.value = ${selectedDays.join(', ')} ${start}–${end};
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
        });
    }

    // Initial check on page load
    toggleCoordinatorInputs();

    // Change event
    coordinatorSelect.addEventListener("change", toggleCoordinatorInputs);
});

  
</script>
</html>

