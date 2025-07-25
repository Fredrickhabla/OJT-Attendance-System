<?php
session_start();
require_once '../connection.php';
require_once '../logger.php'; 
$profile_picture = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'unknown';
    if (!$user_id) {
        die("User not logged in.");
    }

    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadFileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $relativePath = "uploads/coordinators/" . $uploadFileName;
        $absolutePath = __DIR__ . "/../" . $relativePath;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $absolutePath)) {
            $profile_picture = $relativePath;
        }
    }

    $coordinator_id = 'coord_' . uniqid();

    $stmt = $conn->prepare("INSERT INTO coordinator (coordinator_id, name, position, email, phone, profile_picture, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $coordinator_id, $name, $position, $email, $phone, $profile_picture, $user_id);

    if ($stmt->execute()) {
      
        $description = "Coordinator profile created: $coordinator_id";
        logTransaction($conn, $user_id, $username, $description, $username);

        
        $activity = "New Coordinator profile";
        $new_value = json_encode([
            'coordinator_id' => $coordinator_id,
            'name' => $name,
            'position' => $position,
            'email' => $email,
            'phone' => $phone,
            'profile_picture' => $profile_picture
        ]);
        $old_value = '-'; 
        logAudit($conn, $user_id, $activity, $new_value, $old_value, $username);

        header("Location: coorddashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Coordinator Profile</title>
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      background-color: #f0f2f5;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
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
      margin-bottom: 18px;
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
    <div class="card">
      <div class="card-header">
        <h2 class="h2coord">Coordinator Profile</h2>
      </div>
      <div class="card-content">

        <form class="form" method="POST" enctype="multipart/form-data">
          <div class="avatar-wrapper">
            <?php if (!empty($profile_picture)): ?>
              <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Avatar" class="avatar" />
            <?php else: ?>
              <img src="/ojtform/images/placeholder.jpg" alt="Avatar Placeholder" class="avatar" />

            <?php endif; ?>

            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" />
            <button type="button" class="insert-photo-btn" onclick="document.getElementById('profile_picture').click();">Insert Photo</button>
          </div>

          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required/>
          </div>
          <div class="form-group">
            <label for="position">Position</label>
            <input type="text" id="position" name="position" required/>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required/>
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" required/>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">Save</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</body>
</html>
<script>
  const input = document.getElementById('profile_picture');
  const avatarWrapper = document.querySelector('.avatar-wrapper');

  input.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
      const img = document.createElement('img');
      img.className = 'avatar';
      img.src = URL.createObjectURL(file);

      const fallback = avatarWrapper.querySelector('.avatar-fallback');
      if (fallback) fallback.remove();

      const existingImg = avatarWrapper.querySelector('img.avatar');
      if (existingImg) existingImg.remove();

      avatarWrapper.insertBefore(img, avatarWrapper.querySelector('.insert-photo-btn'));
    }
  });
</script>
