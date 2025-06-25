<?php
$coordinators = [

  [
    "name" => "Dr. Raymond Dioses Santiago",
    "position" => "School Coordinator - Polytechnic University of the Philippines",
    "email" => "raymond.santiago@pup.edu.ph",
    "phone" => "09175551234",
    "address" => "College of Engineering, PUP Sta. Mesa, Manila",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "trainees" => ["Juan Dela Cruz", "Anna Reyes"]
  ],
  [
    "name" => "Mr. Richard Regala",
    "position" => "School Coordinator - Pamantasan ng Lungsod ng Maynila",
    "email" => "richard.regala@plm.edu.ph",
    "phone" => "09287773322",
    "address" => "PLM Campus, Intramuros, Manila",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "trainees" => ["Maria Santos", "Luisa Tan"]
  ],

  [
    "name" => "Mr. Richard Regala",
    "position" => "School Coordinator - Pamantasan ng Lungsod ng Maynila",
    "email" => "richard.regala@plm.edu.ph",
    "phone" => "09287773322",
    "address" => "PLM Campus, Intramuros, Manila",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "trainees" => ["Maria Santos", "Luisa Tan"]
  ],

  [
    "name" => "Mr. Richard Regala",
    "position" => "School Coordinator - Pamantasan ng Lungsod ng Maynila",
    "email" => "richard.regala@plm.edu.ph",
    "phone" => "09287773322",
    "address" => "PLM Campus, Intramuros, Manila",
    "image" => "/ojtform/images/sampleprofile.jpg",
    "trainees" => ["Maria Santos", "Luisa Tan"]
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Coordinator - OJT ACER</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
    body { background-color: #f4f6f9; color: #333; }
    .container { display: flex; height: 100vh; }

    .sidebar {
      width: 300px;
      background-color: #44830f;
      color: white;
      padding: 24px;
    }
    .sidebar h1 { font-size: 22px; margin-bottom: 40px; }
    .menu-label { text-transform: uppercase; font-size: 13px; letter-spacing: 1px; margin-bottom: 16px; opacity: 0.8; }
    .nav { display: flex; flex-direction: column; gap: 8px; }
    .nav a {
      display: flex; align-items: center; padding: 10px 16px;
      color: white; text-decoration: none; border-radius: 4px;
    }
    .nav a:hover { background-color: #14532d; }
    .nav svg { margin-right: 8px; }
    .acerlogo { text-align: center; font-size: 20px; }

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
      margin: 10px 20px;
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

.coordinator-card {
  display: flex;
  align-items: center;
  background-color: white;
  border-radius: 10px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  padding: 16px;
  gap: 24px;
  opacity: 0;
  transform: translateY(40px);
  animation: fadeSlideIn 0.6s ease-out forwards;
}

.coordinator-card:nth-child(1) { animation-delay: 0.1s; }
.coordinator-card:nth-child(2) { animation-delay: 0.2s; }
.coordinator-card:nth-child(3) { animation-delay: 0.3s; }

.coordinator-card img {
  width: 140px;
  height: 140px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.coordinator-card img:hover {
  transform: scale(1.05);
}

.coordinator-details {
  flex: 2;
}

.coordinator-details h2 {
  margin-bottom: 8px;
  color: #166534;
  font-size: 20px;
}

.coordinator-details p {
  margin: 4px 0;
  font-size: 15px;
}

.coordinator-details span.label {
  font-weight: bold;
  color: #333;
}

.trainees {
  flex: 1;
  background-color: #f8f8f8;
  border-radius: 8px;
  padding: 12px 16px;
}

.trainees .trainee-label {
  font-weight: bold;
  display: block;
  margin-bottom: 6px;
  color: #14532d;
}

.trainees ul {
  padding-left: 20px;
  margin: 0;
}

.trainees li {
  margin-bottom: 4px;
}

#searchInput:focus {
  border-color: #166534;
  box-shadow: 0 0 4px rgba(22, 101, 52, 0.3);
}

.search-container {
  margin-left: auto;
  position: relative;
  margin-right: 36px;
}

.search-input {
  padding: 8px 12px 8px 36px;
  border-radius: 20px;
  border: 1px solid #ccc;
  font-size: 14px;
  width: 240px;
  outline: none;
}

.search-icon {
  position: absolute;
  top: 50%;
  left: 10px;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #888;
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
        </svg> Dashboard
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
        </svg> Coordinator
      </a>
      <a href="report.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h6M9 7h.01M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        </svg> Report
      </a>
    </nav>
  </aside>

  <!-- Main Content Area -->
  <div style="flex: 1; display: flex; flex-direction: column;">
    <div class="topbar">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="30" height="30">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>&nbsp; COORDINATOR

    <div class="search-container">
  <input type="text" id="searchInput" class="search-input" placeholder="Search by name...">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none"
       viewBox="0 0 24 24" stroke="currentColor" class="search-icon">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z" />
  </svg>
</div>

    </div>

    <main class="main">
      <?php foreach ($coordinators as $coor): ?>
  <div class="coordinator-card">
    <img src="<?= $coor['image'] ?>" alt="Profile">
    
    <div class="coordinator-details">
      <h2><?= $coor['name'] ?></h2>
      <p><span class="label">Position:</span> <?= $coor['position'] ?></p>
      <p><span class="label">Email:</span> <?= $coor['email'] ?></p>
      <p><span class="label">Phone:</span> <?= $coor['phone'] ?></p>
      <p><span class="label">Address:</span> <?= $coor['address'] ?></p>
    </div>

    <div class="trainees">
      <span class="trainee-label">Trainees:</span>
      <ul>
        <?php foreach ($coor['trainees'] as $trainee): ?>
          <li><?= $trainee ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endforeach; ?>

    </main>
  </div>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const coordinatorCards = document.querySelectorAll('.coordinator-card');

  searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase();

    coordinatorCards.forEach(card => {
      const name = card.querySelector('h2').textContent.toLowerCase();
      card.style.display = name.includes(query) ? 'flex' : 'none';
    });
  });
</script>
</body>
</html>

