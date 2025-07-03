<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Optional fallback for full_name
$full_name = isset($_SESSION["full_name"]) ? $_SESSION["full_name"] : "User";

// Connect to database
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $date = $_POST["date"];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $hours = $_POST["hours"];
    $work_description = trim($_POST["work_description"]);

    // Handle file upload
    $signature_path = "";
    if (!empty($_FILES["signature"]["name"])) {
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

    if (empty($error)) {
        // Use the correct table name
        $stmt = $conn->prepare("INSERT INTO attendance_records (user_id, date, time_in, time_out, hours, work_description, signature_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiss", $user_id, $date, $time_in, $time_out, $hours, $work_description, $signature_path);

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
  <title>User Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/umd/lucide.min.css">
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

    .container {
      background: white;
      border-radius: 24px;
      padding: 40px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      text-align: center;
    }

    .header img {
      width: 80px;
      margin-bottom: 10px;
    }

    .header h1 {
      color: #00bf63;
      margin: 10px 0 0 0;
      font-size: 28px;
    }

    .header p {
      margin: 4px 0 20px;
      font-size: 16px;
      color: #555;
    }

    form {
      text-align: left;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
      color: #333;
    }

    input[type="date"],
    input[type="time"],
    input[type="number"],
    input[type="file"],
    textarea {
      width: 100%;
      border: 2px solid #00bf63;
      border-radius: 10px;
      padding: 10px;
      font-size: 16px;
      margin-top: 5px;
      transition: border 0.3s;
    }

    input[readonly] {
      background: #f5f5f5;
    }

    input:focus,
    textarea:focus {
      border-color: #00994d;
      outline: none;
    }

    button {
      margin-top: 20px;
      width: 100%;
      background: #00bf63;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 999px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
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


    </style>

<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/9131/9131529.png" alt="Profile" class="profile-pic" />
        <h2>Raymond Dioses</h2>
        <p>raymond.dioses@gmail.com</p>
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
      <a href="attendance_form.php">
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
      <a href="#">
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
  <a href="#">
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
    <div class="topbar">Attendance Form </div>
    <div class="main">
        <div class="container">
    <div class="header">
      <img src="images/ojtlogo.png" alt="AcerOJT Logo" />
      <h1>Attendance Form</h1>
      <p>Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></p>
    </div>

    <?php if (!empty($success)): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="success.php" enctype="multipart/form-data">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required />

      <label for="time_in">Time In</label>
      <input type="time" id="time_in" name="time_in" required onchange="calculateHours()" />

      <label for="time_out">Time Out</label>
      <input type="time" id="time_out" name="time_out" required onchange="calculateHours()" />

      <label for="hours">No. of Hours</label>
      <input type="number" id="hours" name="hours" readonly />

      <label for="work_description">Work Description</label>
      <textarea id="work_description" name="work_description" rows="3" placeholder="Describe your work today" required></textarea>

      <label for="signature">E-Signature Image</label>
      <input type="file" id="signature" name="signature" accept="image/*" />

      <button type="submit">Submit Attendance</button>
    </form>
  </div>
      

      
    </div>
  </div>
</div>
</body>

<script>
  
</script>


</html>
