<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: indexv2.php");
    exit();
}

// Get user_id from session
$user_id = $_SESSION["user_id"];

// Connect to database
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch trainee's name and email using user_id
$full_name = "User"; // Default fallback
$email = "";

$stmt = $conn->prepare("SELECT first_name, surname, email, profile_picture FROM trainee WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$trainee_info = $result->fetch_assoc();

if ($trainee_info) {
    $full_name = $trainee_info['first_name'] . ' ' . $trainee_info['surname'];
    $email = $trainee_info['email'];

    $raw_picture = trim($trainee_info['profile_picture']);
    $local_path = $raw_picture; 

    // ✅ Check both value and if file actually exists on disk
    if (!empty($raw_picture) && file_exists($local_path)) {
        $profile_picture = $local_path;
    } else {
        $profile_picture = "https://cdn-icons-png.flaticon.com/512/9131/9131529.png";
    }
} else {
    $full_name = "Unknown Trainee";
    $profile_picture = "https://cdn-icons-png.flaticon.com/512/9131/9131529.png";
}


$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $hours = $_POST["hours"];
    $work_description = trim($_POST["work_description"]);

    

    // ✅ Get trainee_id from user_id
    $trainee_id = null;
    $trainee_query = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
    $trainee_query->bind_param("s", $user_id);
    $trainee_query->execute();
    $trainee_query->bind_result($trainee_id);
    $trainee_query->fetch();
    $trainee_query->close();

    if (!$trainee_id) {
        $error = "Trainee not found for the current user.";
    }

    // ✅ Handle file upload
    $signature_path = "";
    if (empty($error) && !empty($_FILES["signature"]["name"])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = basename($_FILES["signature"]["name"]);
        $target_file = $upload_dir . uniqid() . "_" . $filename;

        if (move_uploaded_file($_FILES["signature"]["tmp_name"], $target_file)) {
            $signature_path = $target_file;
        } else {
            $error = "Failed to upload signature image.";
        }
    }

    // ✅ Save attendance
    if (empty($error)) {
        $attendance_id = 'attendance_' . time() . bin2hex(random_bytes(2));

        if (strlen($time_in) === 5) {
    $time_in .= ":00"; // Make it HH:MM:SS
}
if (strlen($time_out) === 5) {
    $time_out .= ":00";
}

        $stmt = $conn->prepare("INSERT INTO attendance_record (attendance_id, trainee_id, date, time_in, time_out, hours, work_description, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiss", $attendance_id, $trainee_id, $date, $time_in, $time_out, $hours, $work_description, $signature_path);

        if ($stmt->execute()) {
            $success = "Attendance submitted successfully.";

        } else {
            $error = "Error saving attendance: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Form</title>
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
    justify-content: center;
    align-items: center;    
    display: flex;
    margin-top: 20px;
}
.content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

.topbar {
      background-color: #14532d;
      color: white;
      padding: 16px;
      font-size: 20px;
      font-weight: bold;
      width: 100%;
    }
    /* Main */
    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    
    .main {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 20px;
      padding: 0px 20px;
    
    }
    .container {
      background: white;
  border-radius: 28px;
  padding: 30px;
  width: 100%;
  max-width: 800px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
   overflow: hidden;
   margin-bottom: 20px;
   height: 90%;

    }
    .header {
      text-align: center;
      margin-bottom: 30px;
    }
    .header img {
      width: 60px;
      margin-bottom: 10px;
    }
    .header h1 {
      color: #3b8c2a;
      font-size: 24px;
      margin: 0;
    }
    .header p {
      font-size: 16px;
      color: #333;
      margin: 4px 0 0;
    }
    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px 20px;
    }
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
    }
    input[type="date"],
    input[type="time"],
    input[type="number"],
    input[type="file"],
    textarea {
      width: 100%;
      border: 2px solid #3b8c2a;
      border-radius: 8px;
      padding: 10px;
      font-size: 14px;
      transition: border 0.3s;
    }
    input:focus,
    textarea:focus {
      border-color: #00bf63;
      outline: none;
    }
    input[readonly] {
      background: #f5f5f5;
    }
    textarea {
      resize: vertical;
      height: 40px;
    }
    .full-width {
      grid-column: span 2;
      text-align: center;
    }
    button {
      background: #00bf63;
      color: white;
      border: none;
      padding: 14px;
      font-size: 16px;
      font-weight: bold;
      border-radius: 999px;
      cursor: pointer;
      transition: background 0.3s;
      width: 300px;
    }
    button:hover {
      background: #00994d;
    }
    .message {
      margin-bottom: 20px;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
    }
    .message.success {
      background: #e6f9ed;
      color: #007a3d;
    }
    .message.error {
      background: #ffe6e6;
      color: #b30000;
    }
    @media (max-width: 700px) {
      form {
        grid-template-columns: 1fr;
      }
      .full-width {
        grid-column: span 1;
      }
      button {
        width: 100%;
      }
    }
.time-pair-row {
  display: flex;
  gap: 19px;
  grid-column: span 2; 
  align-items: flex-end;
}

.time-small {
  flex: 1;
  max-width: 165px; 
}
#signature {
  width: 118%;
  height: 50px; /* Same height as work_description box */
  padding: 10px;
  font-size: 15px;
  border: 2px solid #3b8c2a;
  border-radius: 8px;
  background-color: white;
  cursor: pointer;
  box-sizing: border-box;
}
#work_description {
  height: 100px;
  width: 206%;
  padding: 10px;
  font-size: 15px;
  border: 2px solid #3b8c2a;
  border-radius: 8px;
  resize: none;
  box-sizing: border-box;
}

.WOW {
  margin-bottom: 40px;
}

  </style>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const timeInInput = document.getElementById("time_in");
  const timeOutInput = document.getElementById("time_out");
  const hoursInput = document.getElementById("hours");

  function calculateHours() {
    const timeIn = timeInInput.value;
    const timeOut = timeOutInput.value;

    // 💡 Show values in console
    console.log("Time In:", timeIn);
    console.log("Time Out:", timeOut);

    if (!timeIn || !timeOut) {
      hoursInput.value = "";
      return;
    }

    const [inHour, inMin] = timeIn.split(":").map(Number);
    const [outHour, outMin] = timeOut.split(":").map(Number);

    // 💡 Check if the parsed numbers are valid
    if (
      isNaN(inHour) || isNaN(inMin) ||
      isNaN(outHour) || isNaN(outMin)
    ) {
      console.warn("Invalid time format");
      hoursInput.value = "";
      return;
    }

    // Construct date objects
    const inDate = new Date(2000, 0, 1, inHour, inMin);
    const outDate = new Date(2000, 0, 1, outHour, outMin);

    // Handle overnight shift
    if (outDate < inDate) {
      outDate.setDate(outDate.getDate() + 1);
    }

    const diffMs = outDate - inDate;
    const diffMinutes = diffMs / 1000 / 60;
    const totalHours = Math.floor(diffMinutes / 60); 

    console.log("Calculated Decimal Hours:", totalHours);
    hoursInput.value = totalHours;
  }

  timeInInput.addEventListener("input", calculateHours);
  timeOutInput.addEventListener("input", calculateHours);

  // If inputs are pre-filled
  calculateHours();
});
</script>



</head>
<body>
<div class="dashboard">
  <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
        <img src="<?= htmlspecialchars($profile_picture) . '?v=' . time() ?>" alt="Profile" class="profile-pic" />

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
    <div class="main">
      <div class="container">
        <div class="header">
          <img src="images/ojtlogo.png" alt="Logo">
          <h1>Attendance Form</h1>
          <p>Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></p>
        </div>
        <?php if (!empty($success)): ?>
          <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
          <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="WOW">
        <div>
          <label for="date">Date</label>
          <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required />
        </div>
        <div>
          <label for="hours">No. of Hours</label>
          <input type="number" id="hours" name="hours" readonly />
        </div>
        <div class="time-pair-row">
          <div class="time-small">
            <label for="time_in">Time In</label>
            <input type="time" id="time_in" name="time_in" value="<?= isset($_POST['time_in']) ? $_POST['time_in'] : '' ?>" required />
          </div>
          <div class="time-small">
            <label for="time_out">Time Out</label>
            <input type="time" id="time_out" name="time_out" value="<?= isset($_POST['time_out']) ? $_POST['time_out'] : '' ?>" required />
          </div>
      <div>
        <label for="signature">E-Signature Image</label>
        <input type="file" id="signature" name="signature" accept="image/*" />
        </div>
        </div>
      <div>
        <label for="work_description">Work Description</label>
        <textarea id="work_description" name="work_description" required></textarea>
      </div>
      <div class="full-width">
        <button type="submit">Submit Attendance</button>
      </div>
      </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
