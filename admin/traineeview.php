<?php
// Array of trainee data
$trainees = [
  1 => [
    "name" => "Juan Dela Cruz",
    "email" => "juan.delacruz@example.com",
    "school" => "Pamantasan ng Lungsod ng Maynila",
    "phone" => "09212497344",
    "address" => "Pandacan, Manila",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "schedule" => "M–F, 8:00 AM – 5:00 PM",
    "required_hours" => 500,
    "completed_hours" => 320
    
  ],
  2 => [
    "name" => "Maria Santos",
    "email" => "maria.santos@example.com",
    "school" => "De La Salle University",
    "phone" => "09181234567",
    "address" => "Makati City",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "schedule" => "M, W, F – 9:00 AM – 4:00 PM",
    "required_hours" => 420,
    "completed_hours" => 100
  ],
  3 => [
    "name" => "Pedro Ramirez",
    "email" => "pedro.ramirez@example.com",
    "school" => "University of the Philippines",
    "phone" => "09331234567",
    "address" => "Quezon City",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "schedule" => "T, Th – 10:00 AM – 6:00 PM",
    "required_hours" => 500,
    "completed_hours" => 320
  ],
  4 => [
    "name" => "Anna Reyes",
    "email" => "anna.reyes@example.com",
    "school" => "Ateneo de Manila University",
    "phone" => "09279876543",
    "address" => "Pasig City",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "schedule" => "M–F, 8:00 AM – 5:00 PM",
    "required_hours" => 300,
    "completed_hours" => 80
  ],
  5 => [
    "name" => "Mark Villanueva",
    "email" => "mark.villanueva@example.com",
    "school" => "Far Eastern University",
    "phone" => "09093456789",
    "address" => "Taguig City",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "schedule" => "M, T, Th – 9:00 AM – 3:00 PM",
    "required_hours" => 500,
    "completed_hours" => 120
  ],
  6 => [
    "name" => "Luisa Tan",
    "email" => "luisa.tan@example.com",
    "school" => "University of Santo Tomas",
    "phone" => "09112223344",
    "address" => "Caloocan City",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "schedule" => "W–F, 10:00 AM – 5:00 PM",
    "required_hours" => 240,
    "completed_hours" => 120
  ],
];

// Get trainee ID from URL
$id = $_GET['id'] ?? 0;

// Validate ID
if (!isset($trainees[$id])) {
  echo "<h2 style='color:red;'>Trainee not found.</h2>";
  exit;
}

$trainee = $trainees[$id];
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
          <button class="action-btn">Edit Profile</button>
          <button class="action-btn">Delete Profile</button>
          <a href="trainee.php"><button class="action-btn">Return</button></a>
        </div>

      </div>
    </div>
  </div>
</div>

</body>
</html>
