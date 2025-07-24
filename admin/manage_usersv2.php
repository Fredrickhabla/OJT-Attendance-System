<?php
session_start();
include('../conn.php');

$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) &&
   (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: indexv2.php?timeout=1"); 
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: /ojtform/indexv2.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users WHERE active = 'Y' ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

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
    .table-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.1);
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
      <a href="/ojtform/logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </aside>

  <div class="content">
    <div class="topbar">Manage Users</div>
    <div class="main">
      <div class="table-container">
        <h3 class="text-center text-success mb-4"><i class="bi bi-people"></i> User Accounts</h3>

        <?php if ($users): ?>
        <div class="table-responsive">
          <table id="usersTable" class="table table-bordered table-striped">
            <thead class="table-success">
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Email</th>
                <th>Creation</th>
                <th>Approved</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
              <tr>
                <td><?= htmlspecialchars($user['user_id']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td><?= htmlspecialchars($user['is_approved']) ?></td>
                <td class="text-center">
                  <a href="edit_usersv2.php?user_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete_usersv2.php?user_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to arhive?');">
                    <i class="bi bi-archive"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p class="text-center">No users found.</p>
        <?php endif; ?>

        <div class="mt-4 text-end">
          <button type="button" class="btn btn-primary" onclick="window.location.href='report.php'">
            <i class="bi bi-arrow-left"></i> Back to Report
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  $(document).ready(function() {
    $('#usersTable').DataTable({
      "pageLength": 10,
      "order": [[0, "desc"]],
      "language": {
        "search": "Search users:",
        "lengthMenu": "Show _MENU_ entries per page",
        "zeroRecords": "No matching users found",
        "info": "Showing _START_ to _END_ of _TOTAL_ users",
        "infoEmpty": "No users available",
        "infoFiltered": "(filtered from _MAX_ total users)"
      }
    });
  });
</script>
<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
  <script>alert("User updated successfully!");</script>
<?php endif; ?>

</body>
</html>
<script src="/ojtform/autologout.js"></script>