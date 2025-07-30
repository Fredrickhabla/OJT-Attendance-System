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

$limit = 6; // You can change the number of coordinators per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total coordinators
$countSql = "SELECT COUNT(*) as total FROM coordinator WHERE active = 'Y'";
$countResult = $conn->query($countSql);
$totalCoordinators = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCoordinators / $limit);

// Paginated query
$coordinatorQuery = "SELECT * FROM coordinator WHERE active = 'Y' LIMIT $limit OFFSET $offset";
$coordinatorResult = $conn->query($coordinatorQuery);


$coordinators = [];

if ($coordinatorResult->num_rows > 0) {
    while ($coor = $coordinatorResult->fetch_assoc()) {
        $coordinator_id = $coor['coordinator_id'];

        // Get trainees under this coordinator
        $traineeQuery = "SELECT CONCAT(first_name, ' ', surname) AS name, school
                         FROM trainee WHERE coordinator_id = '$coordinator_id'";
        $traineeResult = $conn->query($traineeQuery);

        $trainees = [];
        $address = "N/A";

        while ($trainee = $traineeResult->fetch_assoc()) {
            $trainees[] = ucwords(strtolower($trainee['name']));
            if ($address === "N/A" && !empty($trainee['school'])) {
                $address = $trainee['school'];
            }
        }

        $coordinators[] = [
           "id" => $coordinator_id,
            "name" => $coor['name'],
            "position" => $coor['position'],
            "email" => $coor['email'],
            "phone" => $coor['phone'],
            "address" => $address,
            "image" => !empty($coor['profile_picture']) ? "/ojtform/" . $coor['profile_picture'] : "/ojtform/images/placeholdersquare.jpg",
            "trainees" => $trainees
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Coordinator - OJT ACER</title>
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
  flex: 1.1;
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
.trainee-label{
  font-size: 16px;
}
.trainee-grid {
  display: grid;
  grid-template-columns: 1fr 1fr; /* Two equal columns */
  gap: 4px 20px; /* Adjust vertical/horizontal spacing */
  margin-top: 5px;
}

.trainee-item {
  position: relative;
  padding-left: 10px;
  font-size: 16px;
}

.trainee-item::before {
  content: "•"; /* Bullet symbol */
  position: absolute;
  left: 0;
  top: 0;
  color: black;
}

/* Modal overlay */
#editModal {
  display: none; /* Hidden by default */
  position: fixed;
  top: 0;
  left: 0;
  z-index: 999;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* dark transparent background */
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Modal content box */
#editModal > div {
  background: #ffffff;
  padding: 25px 30px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.25);
  position: relative;
}

/* Close button (X) */
#editModal span {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  color: #333;
}

/* Form labels and inputs */
#editModal form div {
  margin-bottom: 15px;
}

#editModal label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 5px;
}

#editModal input[type="text"],
#editModal input[type="email"],
#editModal input[type="file"] {
  width: 100%;
  padding: 8px 10px;
  font-size: 0.95rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
}

/* Save button */
#editModal button[type="submit"] {
  width: 100%;
  padding: 10px 0;
  background-color: #44830f;
  border: none;
  color: white;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

#editModal button[type="submit"]:hover {
  background-color: #0056b3;
}

.delete-btn {
  background-color: rgb(219, 71, 57);
  color: white;
  padding: 10px 20px;
  text-decoration: none;
  border-radius: 5px;
  display: inline-block;
  font-size: 14px;
}

.delete-btn:hover {
  background-color: rgb(200, 60, 50);
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
        Trainee
      </a>
      <a href="coordinator.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7m0-7l-9-5m9 5l9-5" />
        </svg>
        <strong>Coordinator</strong>
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

  <!-- Main Content Area -->
  <div style="flex: 1; display: flex; flex-direction: column;">
    <div class="topbar">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="30" height="30">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>&nbsp; <strong>COORDINATOR</strong>

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
  <div class="coordinator-card"
     data-search="<?= htmlspecialchars(strtolower(
       $coor['name'] . ' ' .
       $coor['position'] . ' ' .
       $coor['email'] . ' ' .
       $coor['phone'] . ' ' .
       $coor['address'] . ' ' .
       implode(' ', $coor['trainees'])
     ), ENT_QUOTES) ?>"
     onclick='openEditModal(

       "<?= $coor['id'] ?>",
       "<?= htmlspecialchars($coor['name'], ENT_QUOTES) ?>",
       "<?= htmlspecialchars($coor['position'], ENT_QUOTES) ?>",
       "<?= htmlspecialchars($coor['email'], ENT_QUOTES) ?>",
       "<?= htmlspecialchars($coor['phone'], ENT_QUOTES) ?>",
       "<?= htmlspecialchars($coor['address'], ENT_QUOTES) ?>",
       "<?= $coor['image'] ?>"
     )'>
    <img src="<?= $coor['image'] ?>" alt="Profile">
    
    <div class="coordinator-details">
      <h2><?= $coor['name'] ?></h2>
      <p><span class="label">Position:</span> <?= $coor['position'] ?></p>
      <p><span class="label">Email:</span> <?= $coor['email'] ?></p>
      <p><span class="label">Phone:</span> <?= $coor['phone'] ?></p>
      <p><span class="label">School:</span> <?= $coor['address'] ?></p>
    </div>

    <div class="trainees">
      <span class="trainee-label">Trainees:</span>
      <div class="trainee-grid">
    <?php foreach ($coor['trainees'] as $trainee): ?>
      <div class="trainee-item"><?= htmlspecialchars($trainee) ?></div>
    <?php endforeach; ?>
  </div>
    </div>
  </div>

  
<?php endforeach; ?>
<div class="pagination" style="text-align:right; padding: 20px;">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" style="margin-right: 10px;">&laquo; Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>" style="margin: 0 5px; <?= $i === $page ? 'font-weight: bold; text-decoration: underline;' : '' ?>">
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

<div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div style="background:white; padding:20px; border-radius:8px; width:400px; position:relative;">
    <span onclick="closeModal()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-weight:bold">&times;</span>
    <form action="update_coordinator.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="coordinator_id" id="edit_coordinator_id">
      

      <div>
        <label>Photo:</label>
        <input type="file" name="profile_picture">
      </div>
      <div>
  <label>Name:</label>
  <input type="text" name="name" id="edit_name">
</div>
      <div>
        <label>Position:</label>
        <input type="text" name="position" id="edit_position">
      </div>
      <div>
        <label>Email:</label>
        <input type="email" name="email" id="edit_email">
      </div>
      <div>
        <label>Phone:</label>
        <input type="text" name="phone" id="edit_phone">
      </div>
      <div>
        <label>School:</label>
        <input type="text" name="address" id="edit_address" disabled>
      </div>
        <div style="display: flex; gap: 20px;">
          <button type="submit">Save Changes</button>
          <a href="delete_coordinator.php?coordinator_id=<?= htmlspecialchars($coordinator_id) ?>"
   class="delete-btn"
   onclick="return confirm('Are you sure you want to archive this coordinator?');">
   Archive
</a>


        </div>


    </form>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div style="background:white; padding:20px; border-radius:8px; width:400px; position:relative;">
    <span onclick="closeDeleteModal()" style="position:absolute; top:10px; right:15px; cursor:pointer; font-weight:bold">&times;</span>
    <h3>Confirm Archive</h3>
    <p>Are you sure you want to Archive this coordinator?</p>

    <div style="margin-top:15px; font-size:14px; line-height:1.6;">
      <strong>Position:</strong> <span id="delete_position"></span><br>
      <strong>Email:</strong> <span id="delete_email"></span><br>
      <strong>Phone:</strong> <span id="delete_phone"></span><br>
      <strong>Address:</strong> <span id="delete_address"></span>
    </div>
    <form action="delete_coordinator.php" method="POST" onsubmit="return confirm('Are you sure?');">
      <input type="hidden" name="coordinator_id" value="<?= htmlspecialchars($id) ?>">
      <button type="submit" style="background:red;color:white;padding:10px 20px;border:none;border-radius:5px;">Delete</button>
    </form>
  </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
  <script>
    window.onload = function() {
      alert("Coordinator updated successfully!");
    };
  </script>
<?php endif; ?>


</body>

<script>
  const searchInput = document.getElementById('searchInput');
  const coordinatorCards = document.querySelectorAll('.coordinator-card');

searchInput.addEventListener('input', function () {
  const query = this.value.toLowerCase();

  coordinatorCards.forEach(card => {
    const searchContent = card.getAttribute('data-search') || "";
    const match = searchContent.includes(query);
    card.style.display = match ? 'flex' : 'none';
  });
});


  function openEditModal(id, name, position, email, phone, address, image) {
  document.getElementById('editModal').style.display = 'flex';
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_position').value = position;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_phone').value = phone;
  document.getElementById('edit_address').value = address;
  document.getElementById('edit_coordinator_id').value = id;
}

function closeModal() {
  document.getElementById('editModal').style.display = 'none';
}
function openDeleteModal(id, position, email, phone, address) {
  document.getElementById('deleteModal').style.display = 'flex';
  document.getElementById('delete_coordinator_id').value = id;
  document.getElementById('delete_position').textContent = position;
  document.getElementById('delete_email').textContent = email;
  document.getElementById('delete_phone').textContent = phone;
  document.getElementById('delete_address').textContent = address;
}

function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
}

function scrollToTop() {
  const main = document.querySelector('.main');
  if (main) {
    main.scrollTo({ top: 0, behavior: 'smooth' });
  }
}

</script>
<script src="/ojtform/autologout.js"></script>
</html>

