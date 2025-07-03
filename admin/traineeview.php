

<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ojtformv3";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Get trainee ID from URL
$id = $_GET['id'] ?? '';
$id = trim($id); // Keep it as string

// Updated SQL + binding
$sql = "SELECT t.*, u.email 
        FROM trainee t
        LEFT JOIN users u ON t.user_id = u.user_id
        WHERE t.trainee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);  // ← "s" for string
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h2 style='color:red;'>Trainee not found for ID: $id</h2>";

    // Debug: Check all available trainee_ids
    $check = $conn->query("SELECT trainee_id, first_name, surname FROM trainee");
    echo "<h3>Available IDs:</h3><ul>";
    while ($r = $check->fetch_assoc()) {
        echo "<li>{$r['trainee_id']}: {$r['first_name']} {$r['surname']}</li>";
    }
    echo "</ul>";

    exit;
}

$row = $result->fetch_assoc();

// Format name
$name = ucwords(strtolower($row["first_name"] . ' ' . $row["surname"]));

// Format address to show last two parts
$addressParts = array_map('trim', explode(',', $row["address"]));

if (count($addressParts) >= 2) {
    $lastTwoParts = array_slice($addressParts, -2);
} else {
    $lastTwoParts = $addressParts; // fallback if not enough parts
}

$formattedAddress = implode(', ', array_map(fn($part) => ucwords(strtolower($part)), $lastTwoParts));


// Profile picture fallback
$image = !empty($row["profile_picture"]) ? "/ojtform/" . $row["profile_picture"] : "/ojtform/images/sampleprofile.jpg";


if (!empty($row["schedule_days"]) && !empty($row["schedule_start"]) && !empty($row["schedule_end"])) {
    $formattedStart = date("g:i A", strtotime($row["schedule_start"])); // e.g., 8:00 AM
    $formattedEnd = date("g:i A", strtotime($row["schedule_end"]));     // e.g., 5:00 PM
    $dayMap = [
    "m" => "M",
    "t" => "T",
    "w" => "W",
    "th" => "TH",
    "f" => "F"
];

$scheduleDaysRaw = strtolower($row["schedule_days"]);
$splitDays = array_map('trim', explode(',', $scheduleDaysRaw));
$mappedDays = [];

foreach ($splitDays as $day) {
    $cleanDay = strtolower(trim($day));
    if (isset($dayMap[$cleanDay])) {
        $mappedDays[] = $dayMap[$cleanDay];
    }
}


$finalDays = implode(', ', $mappedDays);
$schedule = !empty($finalDays) ? "$finalDays ($formattedStart - $formattedEnd)" : "Not set";
} else {
    $schedule = "Not set";
}

// Store all needed info into $trainee (to keep rest of HTML unchanged)
$trainee = [
    "name" => $name,
    "email" => $row["email"],
    "school" => ucwords(strtolower($row["school"] ?? "Unknown")),
    "phone" => $row["phone_number"],
    "address" => $formattedAddress,
    "image" => $image,
    "schedule" => $schedule,
    "required_hours" => (int) $row["required_hours"] ?? 0,
   "completed_hours" => isset($row["completed_hours"]) ? (int) $row["completed_hours"] : 20,
];




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>View Trainee - <?= htmlspecialchars($trainee['name']) ?></title>
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

    .acerlogo {
      text-align: center;
      font-size: 20px;
    }

    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .topbar {
      background-color: #14532d;
      color: white;
      padding: 16px;
      font-size: 20px;
      font-weight: bold;
    }

    .main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }

    .profile-box {
      background: white;
      padding: 30px 40px;
      border-radius: 24px;
      border: 4px solid #2e7d32;
      text-align: center;
      width: 1000px;
      max-width: 100%;

      /* Updated to column flex so button group can be below */
      display: flex;
      flex-direction: column;
      gap: 40px;
    }

    .profile-box > .info-progress-wrapper {
      display: flex;
      gap: 0px;
    }

    .profile-img {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 12px;
      margin-left: 60px;
    }

    .add-photo-btn {
      background-color: #2e7d32;
      color: white;
      border: none;
      padding: 6px 16px;
      border-radius: 999px;
      font-size: 14px;
      cursor: pointer;
      margin-bottom: 20px;
    }

    .info-row {
      text-align: left;
      margin: 8px 0;
      display: flex;
      font-size: 16px;
    }

    .info-row b {
      width: 150px;
      display: inline-block;
    }

    .btn-group {
      margin-top: 2px;
      width: 100%;
      justify-content: center;
      display: flex; /* added to center buttons horizontally */
      gap: 20px;
    }

    .action-btn {
      background-color: #10b981;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 999px;
      font-size: 14px;
      min-width: 140px;
      cursor: pointer;
    }

    .action-btn:hover {
      background-color: #059669;
    }

    /* Modal backdrop */
#editModal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.5);
}

/* Modal box */
#editModal .modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 20px 30px;
  border: 1px solid #888;
  width: 90%;
  max-width: 500px;
  border-radius: 10px;
  position: relative;
  animation: fadeIn 0.3s ease;
}

/* Close button (X) */
#editModal .close {
  color: #aaa;
  position: absolute;
  top: 10px;
  right: 20px;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}
#editModal .close:hover {
  color: #000;
}

/* Form inputs */
#editModal form label {
  display: block;
  margin-top: 10px;
  font-weight: bold;
}

#editModal form input[type="text"],
#editModal form input[type="email"],
#editModal form input[type="time"] {
  width: 100%;
  padding: 8px;
  margin-top: 4px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

/* Save button */
#editModal form button[type="submit"] {
  margin-top: 15px;
  background-color: #28a745;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
}
#editModal form button[type="submit"]:hover {
  background-color: #218838;
}

/* Optional animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
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
      <a href="#">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        </svg>
        Report
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="content">
    <div class="topbar">Trainee - <?= htmlspecialchars($trainee['name']) ?></div>
    <div class="main">
      <div class="profile-box">

        <div class="info-progress-wrapper">
          <!-- Left: Trainee Info -->
          <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
              <img src="<?= htmlspecialchars($trainee['image']) ?>" alt="Profile Picture" class="profile-img" />
              <button class="add-photo-btn">Add Photo</button>
            </div>

            <div class="info-row"><b>Full Name:</b> <?= htmlspecialchars($trainee['name']) ?></div>
            <div class="info-row"><b>Email Address:</b> <?= htmlspecialchars($trainee['email']) ?></div>
            <div class="info-row"><b>School:</b> <?= htmlspecialchars($trainee['school']) ?></div>
            <div class="info-row"><b>Phone Number:</b> <?= htmlspecialchars($trainee['phone']) ?></div>
            <div class="info-row"><b>Address:</b> <?= htmlspecialchars($trainee['address']) ?></div>
            <div class="info-row"><b>Schedule:</b> <?= htmlspecialchars($trainee['schedule']) ?></div>
          </div>

          <!-- Right: Progress Tracker -->
          <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <?php
              $requiredHours = $trainee['required_hours'];
$completedHours = $trainee['completed_hours'];
$remainingHours = $requiredHours - $completedHours;
$percentage = round(($completedHours / $requiredHours) * 100);

            ?>
            <h3 style="margin-bottom: 16px; font-size: 20px; color: #2e7d32;">Progress Overview</h3>

            <div style="position: relative; width: 160px; height: 160px;">
              <svg viewBox="0 0 36 36" style="width: 100%; height: 100%;">
                <path
                  stroke="#eee"
                  stroke-width="4"
                  fill="none"
                  d="M18 2.0845
                     a 15.9155 15.9155 0 0 1 0 31.831
                     a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <path
                  stroke="#43a047"
                  stroke-width="4"
                  stroke-dasharray="<?= $percentage ?>, 100"
                  fill="none"
                  d="M18 2.0845
                     a 15.9155 15.9155 0 0 1 0 31.831
                     a 15.9155 15.9155 0 0 1 0 -31.831"
                />
                <text x="18" y="20.35" font-size="7" text-anchor="middle" fill="#333"><?= $percentage ?>%</text>
              </svg>
            </div>

            <div style="margin-top: 20px; font-size: 16px; text-align: center;">
              <b>Required Time:</b> <?= $requiredHours ?> hrs<br />
              <b>Completed:</b> <?= $completedHours ?> hrs<br />
              <b>Time Left:</b> <?= $remainingHours ?> hrs
            </div>
          </div>
        </div>

        <div class="btn-group">
          <button class="action-btn" id="editBtn">Edit Profile</button>
          <button class="action-btn">Delete Profile</button>
          <a href="trainee.php"><button class="action-btn">Return</button></a>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Edit Form Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#00000080; z-index:999;">
  <div style="background:white; padding:20px; max-width:500px; margin:100px auto; border-radius:10px; position:relative;">
    <span id="closeModal" style="position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer;">&times;</span>
    <h3>Edit Trainee Information</h3>
    <form method="POST" action="update_trainee.php">
      <input type="hidden" name="trainee_id" value="<?= htmlspecialchars($id) ?>">

      <label>Full Name:</label>
      <input type="text" name="name" value="<?= htmlspecialchars($trainee['name']) ?>" required>

      <label>Email Address:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($trainee['email']) ?>" required>

      <label>School:</label>
      <input type="text" name="school" value="<?= htmlspecialchars($trainee['school']) ?>" required>

      <label>Phone Number:</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($trainee['phone']) ?>" required>

      <label>Address:</label>
      <input type="text" name="address" value="<?= htmlspecialchars($row["address"]) ?>" required>

      <label>Schedule:</label>
      <input type="text" name="schedule_days" value="<?= htmlspecialchars($row["schedule_days"]) ?>" placeholder="e.g., M, T, W" required>
      <input type="time" name="schedule_start" value="<?= htmlspecialchars($row["schedule_start"]) ?>" required>
      <input type="time" name="schedule_end" value="<?= htmlspecialchars($row["schedule_end"]) ?>" required>

      <br><br>
      <button type="submit">Save Changes</button>
    </form>
  </div>
</div>


</body>
<script>
document.getElementById('editBtn').addEventListener('click', function () {
  document.getElementById('editModal').style.display = 'block';
});

document.getElementById('closeModal').addEventListener('click', function () {
  document.getElementById('editModal').style.display = 'none';
});

window.onclick = function(event) {
  if (event.target == document.getElementById('editModal')) {
    document.getElementById('editModal').style.display = "none";
  }
};
</script>

</html>
