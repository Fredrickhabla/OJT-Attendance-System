<?php
session_start(); 
include('../connection.php');
require_once 'logger.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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

$limit = 12; // Number of trainees per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total trainees
$countSql = "SELECT COUNT(*) as total FROM trainee WHERE active = 'Y'";
$countResult = $conn->query($countSql);
$totalTrainees = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalTrainees / $limit);

// Fetch trainees from database
$sql = "SELECT t.*, u.email 
        FROM trainee t
        LEFT JOIN users u ON t.user_id = u.user_id
        WHERE t.active = 'Y'
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$trainees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Normalize full name
        $fullName = ucwords(strtolower($row["first_name"] . ' ' . $row["surname"]));

        // Normalize address
        $fullAddress = $row["address"];

        // Match the last 2 word groups in the address
        if (preg_match('/(?:\b|^)([\w\s]+),?\s+([\w\s]+)$/', $fullAddress, $matches)) {
            $district = ucwords(strtolower(trim($matches[1])));
            $city = ucwords(strtolower(trim($matches[2])));
            $shortAddress = "$district, $city";
        } else {
            $shortAddress = ucwords(strtolower($fullAddress)); // fallback
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
  <title>Trainee - OJT ACER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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

    .logout {
      margin-top: auto;
    }

    .logout a {
      display: flex;
      align-items: center;
      padding: 10px 16px;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: 0.2s;
    }

    .logout a:hover {
      background-color: #2c6b11;
    }

    .bi {
      margin-right: 6px;
    }
    .acerlogo {
      text-align: center;
      font-size: 20px;
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
      text-align: left;
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

    #searchInput:focus {
  border-color: #166534;
  box-shadow: 0 0 4px rgba(22, 101, 52, 0.3);
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  padding: 8px 12px 8px 36px;
  border-radius: 20px;
  border: 1px solid #ccc;
  font-size: 14px;
  width: 240px;
  transition: 0.3s ease;
  outline: none;
  margin-right: 36px;
}

.search-icon {
  position: absolute;
  left: 10px;
  width: 18px;
  height: 18px;
  color: #888;
}

.topbar-space-between {
  justify-content: space-between;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.pagination {
  justify-content: right;
  align-items: right;
}
.pagination a,
.pagination-link {
  display: inline-block;
  padding: 6px 12px;
  border: 1px solid #166534;
  color: #333;
  text-decoration: none;
  border-radius: 4px;
  background-color: white;
  cursor: pointer;
}

.pagination a.active,
.pagination-link.active {
  background-color: #047857;
  color: white;
  font-weight: bold;
}

.pagination a:hover,
.pagination-link:hover {
  background-color: rgb(12, 100, 74);
  color: white;
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
        <strong>Trainee</strong>
      </a>
      <a href="coordinator.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7m0-7l-9-5m9 5l9-5" />
        </svg>
        Coordinator
      </a>
      <a href="report.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        </svg>
        Report
      </a>
      <a href="blogadmin.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h7l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13H7m10-4H7m0 8h4" />
            </svg>
            <span>Blogs</span>
        </a>
      <a href="department.php">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M4 10h16M10 6h4m-7 4v11m10-11v11M12 14v3" />
           </svg>
            <span>Department</span>
        </a>
    </nav>

    <div class="logout">
      <a href="/ojtform/logout.php">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </a>
    </div>
  </aside>

  <!-- Main Area -->
  <div style="flex: 1; display: flex; flex-direction: column;">

        <div class="topbar topbar-space-between">
                <div class="topbar-left">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="30" height="30">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <strong>TRAINEE</strong>
            </div>
         <div class="search-container">
  <input type="text" id="searchInput" placeholder="Search by name..." class="search-input">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none"
       viewBox="0 0 24 24" stroke="currentColor" class="search-icon">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z" />
  </svg>
</div>

        </div>
    <main class="main">
      <div class="trainee-grid">
       
        <?php foreach ($trainees as $id => $trainee): ?>
          <div class="trainee-box"
     data-search="<?= strtolower($trainee['name'] . ' ' . $trainee['email'] . ' ' . $trainee['phone'] . ' ' . $trainee['address']) ?>">


            <img src="<?= htmlspecialchars($trainee['image']) ?>" alt="Profile" class="trainee-img">
            <h3 class="trainee-name"><?= htmlspecialchars($trainee['name']) ?></h3>
            <p class="trainee-email"><?= htmlspecialchars($trainee['email']) ?></p>
            <a href="traineeview.php?id=<?= urlencode($trainee['trainee_id']) ?>"><button class="trainee-btn">View Profile</button></a>
            <p class="trainee-contact"><?= htmlspecialchars($trainee['phone']) ?> | <?= htmlspecialchars($trainee['address']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="pagination" style="text-align:right; padding: 20px;">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" style="margin-right: 10px;">&laquo; Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>" style="margin: 0 5px; <?= $i === $page ? 'font-weight: bold;' : '' ?>">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>" style="margin-left: 10px;">Next &raquo;</a>
  <?php endif; ?>

  <a href="#" onclick="scrollToTop(); return false;" class="pagination-link" style="margin-left: 5px;">
  ↑ Page Up
</a>
</div>


    </main>
    

  </div>
</div>



<script>
  const searchInput = document.getElementById('searchInput');
  const traineeBoxes = document.querySelectorAll('.trainee-box');

  searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase();

    traineeBoxes.forEach(box => {
      const searchContent = box.getAttribute('data-search');
    box.style.display = searchContent.includes(query) ? 'block' : 'none';
    });
  });

  function scrollToTop() {
  const main = document.querySelector('.main');
  if (main) {
    main.scrollTo({ top: 0, behavior: 'smooth' });
  }
}
</script>
<script src="/ojtform/autologout.js"></script>
</body>
</html>


