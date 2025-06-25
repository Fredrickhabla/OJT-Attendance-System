<?php
session_start();

/* ── DATABASE CONNECTION ─────────────────────────── */
$host     = "localhost";
$dbname   = "ojtform";   // change if needed
$db_user  = "root";
$db_pass  = "";

$conn = new mysqli($host, $db_user, $db_pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* ── HANDLE FORM SUBMISSION ──────────────────────── */
$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';
    $full_name        = trim($_POST['full_name'] ?? '');
    $position         = trim($_POST['position'] ?? '');
    $training_company = trim($_POST['training_company'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $course_year      = trim($_POST['course_year'] ?? '');
    $owner_manager    = trim($_POST['owner_manager'] ?? '');

    // Required‑field check
    if (
        empty($username) || empty($password) || empty($full_name) ||
        empty($position) || empty($training_company) || empty($address) ||
        empty($course_year) || empty($owner_manager)
    ) {
        $error = "Please fill in all required fields.";
    } else {
        // Username uniqueness
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare(
                "INSERT INTO users
                 (username, password, full_name, position, training_company, address, course_year, owner_manager)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $insert->bind_param(
                "ssssssss",
                $username, $hashed_password, $full_name, $position,
                $training_company, $address, $course_year, $owner_manager
            );

            if ($insert->execute()) {
                $success = "Registration successful! <a href='index.php'>Login now</a>";
            } else {
                $error = "Error occurred while registering. Please try again.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - OJT Attendance</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body {
    background: url('images/cover.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
  }
  .navbar-glass {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(6px);
    box-shadow: 0 2px 6px rgba(0,0,0,.1);
  }
  .register-container {
    max-width: 640px;
    margin: 120px auto;
    background: rgba(255,255,255,0.95);
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,100,0,.2);
  }
  h2   { color:#2e7d32; text-align:center; margin-bottom:20px; }
  label{ color:#2e7d32; }
  .btn-success { background:#2e7d32; border-color:#2e7d32; }
  a    { color:#2e7d32; text-decoration:none; }
  a:hover { color:#1b5e20; text-decoration:none; }
  .alert{ margin-top:10px; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-glass fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="#">OJT ATTENDANCE SYSTEM</a>
  </div>
</nav>

<!-- REGISTRATION FORM -->
<div class="register-container">
  <h2>Register New Trainee</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <!-- Personal Information -->
    <div class="mb-3">
      <label for="full_name" class="form-label">Full Name:</label>
      <input type="text" name="full_name" id="full_name" class="form-control"
             required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="training_company" class="form-label">Company:</label>
      <input type="text" name="training_company" id="training_company" class="form-control"
             required value="<?= htmlspecialchars($_POST['training_company'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="address" class="form-label">Address:</label>
      <input type="text" name="address" id="address" class="form-control"
             required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="course_year" class="form-label">Course/Year:</label>
      <input type="text" name="course_year" id="course_year" class="form-control"
             required value="<?= htmlspecialchars($_POST['course_year'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="owner_manager" class="form-label">Supervisor/Manager:</label>
      <input type="text" name="owner_manager" id="owner_manager" class="form-control"
             required value="<?= htmlspecialchars($_POST['owner_manager'] ?? '') ?>">
    </div>
    <hr>
    <!-- Account Information -->
    <div class="mb-3">
      <label for="position" class="form-label">Position:</label>
      <input type="text" name="position" id="position" class="form-control"
             required value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="username" class="form-label">Username:</label>
      <input type="text" name="username" id="username" class="form-control"
             required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password:</label>
      <div class="input-group">
        <input type="password" name="password" id="password" class="form-control" required>
        <span class="input-group-text bg-white">
          <i class="bi bi-eye-slash" id="togglePassword" style="cursor:pointer;"></i>
        </span>
      </div>
    </div>
    <button type="submit" class="btn btn-success w-100">Register</button>
  </form>
</div>

<!-- JS: Bootstrap bundle + password toggle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggle = document.getElementById('togglePassword');
  const pwd    = document.getElementById('password');
  toggle.addEventListener('click', () => {
    const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
    pwd.setAttribute('type', type);
    toggle.classList.toggle('bi-eye');
    toggle.classList.toggle('bi-eye-slash');
  });
</script>
</body>
</html>
