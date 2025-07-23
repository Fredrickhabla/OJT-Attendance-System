<?php
session_start(); 
$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) &&
   (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /ojtform/indexv2.php?timeout=1"); 
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
require_once '../connection.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}


$coorResult = $conn->query("SELECT coordinator_id, name, position, phone, email, profile_picture FROM coordinator WHERE user_id = '$user_id'");

if (!$coorResult || $coorResult->num_rows === 0) {
    die("Coordinator not found for this user.");
}
$coor = $coorResult->fetch_assoc();
$coordinator_id = $coor['coordinator_id'];
$full_name = $coor['name'];
$email = $coor['email'];
$profile_picture = !empty($coor['profile_picture']) 
    ? '/ojtform/' . $coor['profile_picture'] 
    : '/ojtform/images/placeholder.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $position = $conn->real_escape_string($_POST['position']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

   
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
   
    $uploadFileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $relativePath = "uploads/coordinators/" . $uploadFileName;
    $absolutePath = __DIR__ . "/../" . $relativePath;

    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $absolutePath)) {
        
        $conn->query("UPDATE coordinator SET profile_picture = '$relativePath' WHERE user_id = '$user_id'");
    }
}



    $updateQuery = "UPDATE coordinator 
                    SET name = '$name', position = '$position', email = '$email', phone = '$phone'
                    WHERE user_id = '$user_id'";

    if ($conn->query($updateQuery)) {
    echo "<script>alert('Profile updated successfully.'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
} else {
        echo "<p>Error updating coordinator: " . $conn->error . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports - OJT Attendance Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

    .maincontainer {
        display: flex;
      height: 100vh;
 align-items: center;
      justify-content: center;
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

    .content {
      flex: 1;
      display: flex;
      flex-direction: column;
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


    .bi {
      margin-right: 6px;
    }

 

.card {
      width: 400px;
      background-color: white;
      border-radius: 24px;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
      position: relative;
      overflow: hidden;
    }

    .card-header {
      text-align: center;
      padding: 20px;
      font-size: 20px;
      font-weight: 500;
      border-bottom: 1px solid #eee;
    }

    .card-content {
      padding: 20px;
    }

    .avatar-wrapper {
      position: relative;
      display: flex;
      justify-content: center;
      margin-bottom: 24px;
    }

    .avatar {
      width: 104px;
      height: 104px;
      background-color: #e2e8f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .avatar-fallback {
      background-color: #d1d5db;
      border-radius: 50%;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .icon {
      width: 48px;
      height: 48px;
      color: #9ca3af;
    }

    .insert-photo-btn {
  position: absolute;
  top: 50%;
  right: 30px;
  transform: translateY(-50%);
  background-color: #047857;
  color: white;
  font-size: 12px;
  padding: 6px 10px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
}




    .insert-photo-btn:hover {
      background-color: #065f46;
      
    }

    .form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    label {
      font-size: 14px;
      margin-bottom: 4px;
    }

    input {
      border: 1px solid #047857;
      border-radius: 999px;
      padding: 8px 12px;
      outline: none;
    }

    input:focus {
      border-color: #047857;
      box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.3);
    }

    .form-actions {
      display: flex;
      justify-content: center;
      padding-top: 10px;
    }

    .save-btn {
      background-color: #047857;
      color: white;
      border: none;
      border-radius: 999px;
      padding: 10px 32px;
      cursor: pointer;
      width: 128px;
    }

    .save-btn:hover {
      background-color: #065f46;
    }

    .h2coord{
        font-size: 24px;
        color: #065f46;
        margin: 0;
        text-align: center;

    }


  </style>
</head>
<body>

<div class="container">
  <!-- Sidebar -->
  <aside class="sidebar">
      <div class="profile-section">
  <img src="/ojtform/<?= htmlspecialchars($coor['profile_picture']) ?>" alt="Profile"  class="profile-pic"/>

  <h2><?= htmlspecialchars($full_name) ?></h2>
  <p><?= htmlspecialchars($email) ?></p>
</div>

      <hr class="separator" />
      <nav class="nav-menu">
  <ul>
    <li>
      <a href="coorddashboard.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M3 9L12 2l9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        DASHBOARD
      </a>
    </li>
    <li>
      <a href="dtrmonitoring.php">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M4 4h16v16H4z"/>
          <line x1="8" y1="2" x2="8" y2="22"/>
          <line x1="16" y1="2" x2="16" y2="22"/>
        </svg>
        DTR MONITORING
      </a>
    </li>
    <li>
      <a href="coordupdate.php">
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
      <a href="coordblog.php">
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
 <a href="/ojtform/logout.php">
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
<!-- Topbar -->

<div class="topbar" style="
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    font-size: 18px;
    border: none;
">

    <!-- Department Name -->
    <span>Profile</span>

</div>

<div class="maincontainer">
    <div class="card">
      <div class="card-header">
        <h2 class="h2coord">Coordinator Profile</h2>
      </div>
      <div class="card-content">
        <form class="form" method="POST" enctype="multipart/form-data">
        <div class="avatar-wrapper">
          <div class="avatar">
  <?php if (!empty($coor['profile_picture'])): ?>
    <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Avatar" class="avatar-img" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;" />
    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" />

     <button type="button" class="insert-photo-btn" onclick="document.getElementById('profile_picture').click();">Insert Photo</button>

  <?php else: ?>
    <div class="avatar-fallback">
      <svg class="icon" xmlns="http://www.w3.org/2000/svg" ...>
        <!-- your fallback SVG icon -->
      </svg>
    </div>
  <?php endif; ?>
</div>
        </div>

        

  <div class="form-group">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($full_name) ?>" required/>
  </div>
  <div class="form-group">
    <label for="position">Position</label>
    <input type="text" id="position" name="position" value="<?= htmlspecialchars($coor['position']) ?>" required/>
  </div>
  <div class="form-group">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required/>
  </div>
  <div class="form-group">
    <label for="phone">Phone</label>
    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($coor['phone']) ?>" required/>
  </div>

  <div class="form-actions">
    <button type="submit" class="save-btn">Save</button>
  </div>
</form>

      </div>
    </div>
  </div>

<script>
document.getElementById('profile_picture').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();

        reader.onload = function (event) {
            const avatarImg = document.querySelector('.avatar-img');

           
            if (avatarImg) {
                avatarImg.src = event.target.result;
            } else {
               
                const newImg = document.createElement('img');
                newImg.src = event.target.result;
                newImg.className = 'avatar-img';
                newImg.style.width = '100px';
                newImg.style.height = '100px';
                newImg.style.borderRadius = '50%';
                newImg.style.objectFit = 'cover';

                const avatarDiv = document.querySelector('.avatar');
                avatarDiv.innerHTML = ''; 
                avatarDiv.appendChild(newImg);
            }
        };

        reader.readAsDataURL(file);
    }
});
</script>
<script src="/ojtform/autologout.js"></script>