<?php
session_start();
include('../conn.php');
require_once '../logger.php';

$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) &&
   (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: indexv2.php?timeout=1"); 
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /ojtform/indexv2.php");
    exit;
}

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    die("No user ID provided.");
}

$user_id = trim($_GET['user_id']); // IMPORTANT: do NOT use intval()

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password_hashed   = trim($_POST['password_hashed'] ?? '');
    $role    = trim($_POST['role'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $created_at     = trim($_POST['created_at'] ?? '');
    $is_approved     = trim($_POST['is_approved'] ?? '');

    if (empty($name) || empty($username)) {
        $error = "Name and Username are required.";
    } else {
        // Compare old vs new values
        $old_values = [];
        $new_values = [];

        $fields = [
            'name' => $name,
            'username' => $username,
            'password_hashed' => $password_hashed,
            'role' => $role,
            'email' => $email,
            'created_at' => $created_at,
            'is_approved' => $is_approved
        ];

        foreach ($fields as $key => $new_value) {
            $old_value = $user[$key];
            if ($old_value != $new_value) {
                $old_values[$key] = $old_value;
                $new_values[$key] = $new_value;
            }
        }

    
        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, password_hashed = ?, role = ?, email = ?, created_at = ?, is_approved = ? WHERE user_id = ?");
        $stmt->execute([$name, $username, $password_hashed, $role, $email, $created_at, $is_approved, $user_id]);

        if (!empty($new_values)) {
            $admin_id = $_SESSION['user_id'] ?? 'unknown';
            $admin_name = $_SESSION['username'] ?? 'Unknown Admin';

            logTransaction($pdo, $admin_id, $admin_name, "Updated user account: $user_id", $admin_name);
            logAudit($pdo, $user_id, "Update User Account: $user_id", json_encode($new_values), json_encode($old_values), $admin_name, 'Y');
        }

        header("Location: manage_usersv2.php?update=success");
exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
      color: #333;
      font-family: Arial, sans-serif;
    }
    .layout {
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
    .acerlogo {
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
    }
    .logout a:hover {
      background-color: #2c6b11;
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
    .main {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }
    .form-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
      max-width: 700px;
      margin: auto;
    }
  </style>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div>
      <h1 class="acerlogo"><strong>OJT - ACER</strong></h1>
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
            <path stroke-linecap="round" stroke-line="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Trainee
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
          Blogs
        </a>
        <a href="department.php" style="display: flex; align-items: center; gap: 6px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M4 10h16M10 6h4m-7 4v11m10-11v11M12 14v3" />
          </svg>
          <span>Department</span>
        </a>
      </nav>
    </div>
    <div class="logout">
      <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </aside>
  <div class="content">
    <div class="topbar">Edit User</div>
    <div class="main">
      <div class="form-container">
        <h4 class="text-center text-primary mb-4"><i class="bi bi-pencil-fill"></i> Edit User</h4>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="password_hashed" class="form-label">Password</label>
            <input type="password" name="password_hashed" id="password_hashed" class="form-control" value="<?= htmlspecialchars($user['password_hashed']) ?>">
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <input type="text" name="role" id="role" class="form-control" value="<?= htmlspecialchars($user['role']) ?>">
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
          </div>
          <div class="mb-3">
            <label for="created_at" class="form-label">Created At</label>
            <input type="text" name="created_at" id="created_at" class="form-control" value="<?= htmlspecialchars($user['created_at']) ?>">
          </div>
          <div class="mb-3">
          <label for="is_approved" class="form-label">Is Approved?</label>
          <select name="is_approved" id="is_approved" class="form-control" required>
            <option value="">-- Select --</option>
            <option value="Yes" <?= ($user['is_approved'] === 'Y') ? 'selected' : '' ?>>Yes</option>
            <option value="No" <?= ($user['is_approved'] === 'N') ? 'selected' : '' ?>>No</option>
          </select>
        </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save Changes</button>
            <a href="manage_usersv2.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<script src="/ojtform/autologout.js"></script>