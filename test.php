<?php
session_start();

$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) &&
   (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: indexv2.php?timeout=1"); 
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION["user_id"])) {
    header("Location: indexv2.php");
    exit();
}

$user_id = $_SESSION["user_id"];

require_once 'conn.php';

$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$sys_user = $user['username'] ?? 'unknown_user';

require_once 'logger.php';

$stmt = $pdo->prepare("SELECT * FROM trainee WHERE user_id = ?");
$stmt->execute([$user_id]);
$trainee = $stmt->fetch(PDO::FETCH_ASSOC);


$coordinator = [];
$coordinator_id = '';
if ($trainee && !empty($trainee['coordinator_id'])) {
    $coordinator_id = $trainee['coordinator_id'];
    $stmt = $pdo->prepare("SELECT * FROM coordinator WHERE coordinator_id = ?");
    $stmt->execute([$coordinator_id]);
    $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
}


$stmt = $pdo->query("SELECT * FROM coordinator");
$all_coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);


$disableCoordinatorInputs = false;
foreach ($all_coordinators as $coord) {
    if (
        $coordinator_id &&
        $coord['coordinator_id'] == $coordinator_id &&
        !empty($coord['user_id']) &&
        $coord['user_id'] != $user_id
    ) {
        $disableCoordinatorInputs = true;
        break;
    }
}

$stmt = $pdo->query("SELECT * FROM departments WHERE status = 'active'");
$all_departments = $stmt->fetchAll(PDO::FETCH_ASSOC);



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

if ($traineePicturePath === null && isset($trainee['profile_picture'])) {
    $traineePicturePath = $trainee['profile_picture'];
}


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
      </style>
  

</head>
<body>
<div class="dashboard">
  <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
        <img src="<?= !empty($trainee['profile_picture']) ? htmlspecialchars($trainee['profile_picture']) . '?v=' . time() : '/ojtform/images/placeholder.jpg' ?>" alt="Profile" class="profile-pic" />
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
  <div class="content">
    <div class="topbar">Profile</div>
   
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
            <input type="file" id="coordinator_picture" name="coordinator_picture"
  accept="image/*" style="display: none;" 
  <?= $disableCoordinatorInputs ? 'disabled' : '' ?>>
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
  <div class="tooltip-wrapper">
    <input id="coordName" name="coordName" type="text"
      value="<?= htmlspecialchars($coordinator['name'] ?? '') ?>"
      <?= $disableCoordinatorInputs ? 'disabled' : '' ?> required>
    <?php if ($disableCoordinatorInputs): ?>
      <span class="tooltip-text">You cannot update an existing user coordinator.</span>
    <?php endif; ?>
  </div>
</div>

<div class="form-group">
  <label for="position">Position</label>
  <div class="tooltip-wrapper">
    <input id="position" name="position" type="text"
      value="<?= htmlspecialchars($coordinator['position'] ?? '') ?>"
      <?= $disableCoordinatorInputs ? 'disabled' : '' ?> required>
    <?php if ($disableCoordinatorInputs): ?>
      <span class="tooltip-text">You cannot update an existing user coordinator.</span>
    <?php endif; ?>
  </div>
</div>

<div class="form-group">
  <label for="coordEmail">Email</label>
  <div class="tooltip-wrapper">
    <input id="coordEmail" name="coordEmail" type="email"
      value="<?= htmlspecialchars($coordinator['email'] ?? '') ?>"
      <?= $disableCoordinatorInputs ? 'disabled' : '' ?> required>
    <?php if ($disableCoordinatorInputs): ?>
      <span class="tooltip-text">You cannot update an existing user coordinator.</span>
    <?php endif; ?>
  </div>
</div>

<div class="form-group">
  <label for="phone">Phone</label>
  <div class="tooltip-wrapper">
    <input id="phone" name="phone" type="text"
      value="<?= htmlspecialchars($coordinator['phone'] ?? '') ?>"
      <?= $disableCoordinatorInputs ? 'disabled' : '' ?> required>
    <?php if ($disableCoordinatorInputs): ?>
      <span class="tooltip-text">You cannot update an existing user coordinator.</span>
    <?php endif; ?>
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

    <!-- Change Password Form -->
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

  function syncScheduleToHiddenInputs() {
    const selectedDays = Array.from(dayCheckboxes)
      .filter(cb => cb.checked)
      .map(cb => cb.value)
      .join(',');

    scheduleDaysInput.value = selectedDays;
    scheduleStartInput.value = startTimeInput.value;
    scheduleEndInput.value = endTimeInput.value;
  }


  scheduleInput.addEventListener('click', () => {
    schedulePicker.style.display = schedulePicker.style.display === 'block' ? 'none' : 'block';
  });


  document.addEventListener('click', e => {
    if (!scheduleInput.contains(e.target) && !schedulePicker.contains(e.target)) {
      schedulePicker.style.display = 'none';
    }
  });

  
  dayCheckboxes.forEach(cb => cb.addEventListener('change', updateScheduleInput));
  startTimeInput.addEventListener('input', updateScheduleInput);
  endTimeInput.addEventListener('input', updateScheduleInput);


  document.querySelector('form').addEventListener('submit', syncScheduleToHiddenInputs);

  
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

 
  dayCheckboxes.forEach(cb => {
    if (savedDays.includes(cb.value)) {
      cb.checked = true;
    }
  });


  if (savedStart) startTimeInput.value = savedStart;
  if (savedEnd) endTimeInput.value = savedEnd;

  updateScheduleInput();
});


const togglePasswordCheckbox = document.getElementById("togglePasswordForm");

togglePasswordCheckbox.addEventListener("change", () => {
  const overlay = document.getElementById("passwordOverlay");
  overlay.style.display = togglePasswordCheckbox.checked ? "flex" : "none";
});


function closePasswordForm() {
  document.getElementById("passwordOverlay").style.display = "none";
  document.getElementById("togglePasswordForm").checked = false;
}


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

  
  fetch("change_password.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: current=${encodeURIComponent(current)}&new=${encodeURIComponent(newPass)}
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


document.querySelectorAll('.tooltip-wrapper input[disabled]').forEach(input => {
    const tooltip = input.parentElement.querySelector('.tooltip-text');

    input.addEventListener('mousemove', (e) => {
      if (tooltip) {
        tooltip.style.left = (e.pageX + 10) + 'px';
        tooltip.style.top = (e.pageY + 10) + 'px';
        tooltip.style.opacity = 1;
      }
    });

    input.addEventListener('mouseleave', () => {
      if (tooltip) {
        tooltip.style.opacity = 0;
      }
    });
  });



</script>
<script src="autologout.js"></script>
<?php if ($disableCoordinatorInputs): ?>
  <small style="color: gray; font-style: italic;">
    These fields are disabled because this coordinator is already linked to another user.
  </small>
<?php endif; ?>