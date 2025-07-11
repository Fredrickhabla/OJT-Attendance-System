<?php
$host = "localhost";
$dbname = "ojtformv3";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
require_once 'logger.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student'; 

    if ($name && $username && $email && $pass) {
        try {
            $user_id = uniqid("user_");
            $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
    INSERT INTO users (user_id, name, username, password_hashed, role, email, created_at)
    VALUES (?, ?, ?, ?, ?, ?, CURDATE())
");
$stmt->execute([$user_id, $name, $username, $hashedPassword, $role, $email]);

try {
    logTransaction($pdo, $user_id, $name, "Created new user account", $username);
} catch (Exception $ex) {
    echo "<script>alert('Transaction log failed: " . addslashes($ex->getMessage()) . "');</script>";
}

try {
    logAudit($pdo, $user_id, "User Signup", $email, "-", $username, 'Y');
} catch (Exception $ex) {
    echo "<script>alert('Audit log failed: " . addslashes($ex->getMessage()) . "');</script>";
}


echo "<script>alert('Signup successful!'); window.location.href='indexv2.php';</script>";

        } catch (PDOException $e) {
            logAudit($pdo, 'N/A', "Signup Failed", $email, "-", "system", 'N');
echo "<script>alert('Signup failed: " . $e->getMessage() . "'); history.back();</script>";

        }
    } else {
        echo "<script>alert('Missing required fields.'); history.back();</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acer OJT</title>
  <link rel="stylesheet" href="styles.css"/>
</head>

<style>
/* Reset & base */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: sans-serif;
}
body {
  background: #f0fdf4;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
    opacity: 1;
  transition: opacity 0.6s ease;
}

body.fade-out {
  opacity: 0;
}
/* Wrapper */
.wrapper {
  padding: 20px;
}

/* Main Card */
.card {
  display: flex;
  width: 1280px;
  height: 616.467px;
  border-radius: 28px;
  overflow: hidden;
  box-shadow:
    0 30px 40px rgba(0, 0, 0, 0.35),
    0 15px 20px rgba(0, 0, 0, 0.2);
background: #00bf63;



}

/* Left Panel */
.left-panel {
  background: #00bf63;
  color: white;
  flex: 2;
  padding: 64px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  text-align: center;

}


.welcome-content {
  display: flex;
  flex-direction: column;
  align-items: center;
 
}

.welcome-content h2 {
   font-size: 60px;
  font-weight: 400;
  line-height: 0.8;
  font-family: "Canva Sans";
}
.welcome-content h1 {
    font-size: 74px;
  font-weight: 900;
  margin: 10px 0;
  line-height: 0.6;
  font-family: "Racing Sans One";
}
.welcome-content p {
   font-size: 22px;
  margin-top: 10px;
}
.btn-outline {
  padding: 18px 36px;
  border: 2px solid white;
  background: transparent;
  color: white;
  font-size: 18px;
  border-radius: 999px;
  cursor: pointer;
  width: 240px;
  height: 54px;
}
.btn-outline:hover {
  background: white;
  color: #10b981;
}

.logo-img1 {
  margin-bottom: 60px;
  margin-top:60px;
  width: 140px;
}

/* Right Panel */
.right-panel {
  background: white;
  flex: 34;
  padding-top: 34px;
  padding-right: 64px;
  padding-left: 64px;
  color: #065f46;
  border-top-left-radius: 32px;
  border-bottom-left-radius: 32px;

  
}
.right-panel h2 {
  font-size: 36px;
  margin-bottom: 28px;
}
form .grid-2 {
  display: flex;
  gap: 20px;

}
.field {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
    width: 100%;
}
.field label {
  margin-bottom: 6px;
  font-size: 16px;
}
.field input {
  padding: 14px 18px;
  border: none;
  border-radius: 999px;
  background-color: #ecfdf5;
  font-size: 16px;
}
.checkbox-field {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
  font-size: 16px;
}
.checkbox-field input {
  margin-right: 10px;
}
.submit-wrap {
  text-align: right;
}
.btn-solid {
  padding: 14px 36px;
  background: #065f46;
  color: white;
  font-size: 16px;
  border: none;
  border-radius: 999px;
  cursor: pointer;
  width: 180px;
}
.btn-solid:hover {
  background: #047857;
}

.signuph2 {
  font-size: 36px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 24px;
  margin-top: 10px;
}

.field input {
  width: 100%; /* Add this line */
}

.namelabel {
    width: 500px;
}

.role-options {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.role-options input[type="radio"] {
  display: none;
}

.role-options label {
  padding: 10px 20px;
  border-radius: 999px;
  background-color: #ecfdf5;
  color: #065f46;
  cursor: pointer;
  border: 2px solid transparent;
  font-size: 16px;
  transition: background-color 0.3s, color 0.3s, border 0.3s;
}

.role-options input[type="radio"]:checked + label {
  background-color: #065f46;
  color: white;
  border: 2px solid #047857;
}

</style>
<body>
  <div class="wrapper">
    <div class="card">
      <!-- Left: Green Welcome Section -->
      <div class="left-panel">
        <div class="welcome-content">
          <h2>Welcome to</h2>
          <h1>Acer OJT</h1>
          <p>Proud to serve, proud of Acer</p>
          <img src="images/finalwhitelogo.png" alt="OJT Logo" class="logo-img1">
          <button class="btn-outline transition" data-href="indexv2.php">Sign in</button>
        </div>
      </div>

      <!-- Right: White Signup Form -->
      <div class="right-panel">
        <h2 class="signuph2">Sign up to Acer OJT</h2>
        <form id="signup-form" method="POST" action="signup.php">
  <div class="grid-2">
    <div class="field">
      <label for="name">Name</label>
      <input class="namelabel" type="text" id="name" name="name" required/>
    </div>
    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required/>
    </div>
  </div>
  <div class="field">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required/>
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required/>
  </div>
  <div class="checkbox-field">
    <input type="checkbox" id="terms" required/>
    <label for="terms">By creating an account, you agree to our Terms.</label>
  </div>

  <div class="field">
  <label>Select Role</label>
  <div class="role-options">
    <input type="radio" id="role-trainee" name="role" value="trainee" required>
    <label for="role-trainee">Trainee</label>

    <input type="radio" id="role-admin" name="role" value="admin">
    <label for="role-admin">Admin</label>

    <input type="radio" id="role-coordinator" name="role" value="coordinator">
    <label for="role-coordinator">Coordinator</label>
  </div>
</div>

  
  <div class="submit-wrap">
    <button type="submit" class="btn-solid">Sign up</button>
  </div>
</form>

      </div>
    </div>
  </div>
  <script >

     document.querySelectorAll('a.transition, button.transition').forEach(el => {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      const href = el.getAttribute('href') || el.dataset.href;
      document.body.classList.add('fade-out');
      setTimeout(() => {
        window.location.href = href;
      }, 600); // must match CSS transition duration
    });
  });
    
//   document.getElementById('signup-form').addEventListener('submit', function(e) {
//   e.preventDefault();
//   const name = this.name.value;
//   const username = this.username.value;
//   alert(`Welcome ${name} (${username})! Your sign-up form was submitted.`);
// });

document.querySelectorAll('a.transition, button.transition').forEach(el => {
  el.addEventListener('click', function (e) {
    e.preventDefault();
    const href = el.getAttribute('href') || el.dataset.href;
    document.body.classList.add('fade-out');
    setTimeout(() => {
      window.location.href = href;
    }, 600); // match with CSS duration
  });
});

</script>
</body>
</html>
