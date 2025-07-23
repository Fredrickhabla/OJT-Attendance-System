<?php
session_start();
include('../conn.php');

if (!isset($_SESSION['ValidAdmin']) || $_SESSION['ValidAdmin'] !== true) {
    header("Location: index.php");
    exit;
}

// Get user ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No user ID provided.");
}
$user_id = intval($_GET['id']);

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');

    if (empty($full_name) || empty($username)) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ? WHERE id = ?");
        $stmt->execute([$full_name, $username, $user_id]);
        header("Location: manage_users.php");
        exit;
    }
}

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('../images/cover.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      padding-top: 70px;
    }
    .form-box {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
    }
    .btn-blue {
      background-color: #0d6efd;
      color: white;
      border: none;
    }
    .btn-blue:hover {
      background-color: #0b5ed7;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="dashboard.php">
      <i class="bi bi-people-fill"></i> OJT Attendance System
    </a>
  </div>
</nav>

<div class="container mt-5">
  <div class="form-box">
    <h4 class="text-center text-primary mb-4"><i class="bi bi-pencil-fill"></i> Edit User</h4>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="full_name" class="form-label">Full Name</label>
        <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-blue"><i class="bi bi-save"></i> Save Changes</button>
        <a href="manage_users.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
