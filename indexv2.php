<?php
ob_start();
session_start();

require_once 'connection.php';

require_once 'logger.php';
require_once 'auth_helper.php';
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['quick_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember']);

    if (empty($password) && isset($_COOKIE['rememberme'])) {
        [$identifier, $token] = explode(':', $_COOKIE['rememberme']);

        $stmt = $conn->prepare("SELECT user_id, name, remember_token, role FROM users WHERE username = ? AND remember_identifier = ?");
        $stmt->bind_param("ss", $username, $identifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $name, $hashedToken, $role);
            $stmt->fetch();

            if (password_verify($token, $hashedToken)) {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $role;

                logTransaction($conn, $user_id, $name, "User logged in via cookie", $username);

                if ($role === "admin") {
                    header("Location: admin/dashboardv2.php");
                } else {
                    header("Location: dashboardv2.php");
                }
                exit();
            } else {
                $error = "Remember Me token invalid. Please enter your password.";
            }
        } else {
            $error = "No valid Remember Me token found for this user.";
        }

        $stmt->close();
    } else {
     
        if ($username === "admin" && $password === "admin2314") {
            $_SESSION['user_id'] = "admin";
            $_SESSION['full_name'] = "Admin";
            $_SESSION['role'] = "admin";

            if ($rememberMe) {
                setcookie("remembered_username", "admin", time() + (30 * 24 * 60 * 60), "/", "", false, true);
            }

            header("Location: admin/dashboardv2.php");
            exit();
        }

        $stmt = $conn->prepare("SELECT user_id, name, password_hashed, role, is_approved FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $name, $hashed_password, $role, $is_approved);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                if ($role === 'admin' && $is_approved !== 'Y') {
                    $error = "Please wait until your admin account is approved.";
                    logAudit($conn, $user_id, "Sign In Blocked - Unapproved Admin", "-", "-", $username, 'N');
                } else {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                 
$full_name = $name;

if ($role === 'student') {
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', surname) AS full_name FROM trainee WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_name);
    if ($stmt->fetch()) {
        $full_name = $student_name;
    }
    $stmt->close();
} elseif ($role === 'coordinator') {
    $stmt = $conn->prepare("SELECT name FROM coordinator WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($coordinator_name);
    if ($stmt->fetch()) {
        $full_name = $coordinator_name;
    }
    $stmt->close();
}

logTransaction($conn, $user_id, $full_name, "User signed in successfully", $username);

                    if ($rememberMe) {
                        $identifier = bin2hex(random_bytes(20));
                        $token = generateRandomToken(64);
                        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

                        $updateStmt = $conn->prepare("UPDATE users SET remember_identifier = ?, remember_token = ? WHERE user_id = ?");
                        $updateStmt->bind_param("sss", $identifier, $hashedToken, $user_id);
                        $updateStmt->execute();
                        $updateStmt->close();

                        setRememberMeCookie($identifier, $token);
                        setcookie("remembered_username", $username, time() + (30 * 24 * 60 * 60), "/", "", false, true);
                    }

                    if ($role === 'student') {
                        $checkTrainee = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
                        $checkTrainee->bind_param("s", $user_id);
                        $checkTrainee->execute();
                        $checkTrainee->store_result();

                        if ($checkTrainee->num_rows > 0) {
                            header("Location: dashboardv2.php");
                        } else {
                            header("Location: profile.php");
                        }

                        $checkTrainee->close();
                    } elseif ($role === 'coordinator') {
    $checkCoordinator = $conn->prepare("SELECT * FROM coordinator WHERE user_id = ?");
    $checkCoordinator->bind_param("s", $user_id);
    $checkCoordinator->execute();
    $result = $checkCoordinator->get_result();

    if ($row = $result->fetch_assoc()) {
      
        $requiredFields = ['name', 'position', 'email', 'phone', 'profile_picture'];

        $isComplete = true;
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $isComplete = false;
                break;
            }
        }

        if ($isComplete) {
            header("Location: coordinator/coorddashboard.php");
        } else {
            header("Location: coordinator/coordprofile.php");
        }
    } else {
      
        header("Location: coordinator/coordprofile.php");
    }

    $checkCoordinator->close();
}

elseif ($role === 'admin') {
    header("Location: admin/dashboardv2.php");
    exit();
}
                    exit();
                }
            } else {
                $error = "Invalid username or password.";
                logAudit($conn, $user_id, "Sign In Failed", "-", "-", $username, 'N');
            }
        } else {
            $error = "Invalid username or password.";
            logAudit($conn, "N/A", "Sign In Failed", "-", "-", $username, 'N');
        }

        $stmt->close();
    }
}
?>

<?php
$cookie_username = '';
if (isset($_COOKIE['rememberme'])) {
   
    $cookie_parts = explode(':', $_COOKIE['rememberme']);
    if (count($cookie_parts) === 2) {
        $cookie_username = $_COOKIE['remembered_username'] ?? '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acer OJT Sign In</title>
  <link rel="stylesheet" href="styles.css" />
 
</head>

<style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: sans-serif;
}

body {
  background-color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  opacity: 1;
  transition: opacity 0.6s ease;
}

body.fade-out {
  opacity: 0;
}
.container {
  width: 100%;
  max-width: 1280px;
  background-color: white;
  overflow: hidden;
  box-shadow: 0 30px 40px 5px rgba(0, 0, 0, 0.4);
  border-radius: 32px;
  display: flex;
}
.left {
  flex: 1;
  padding: 48px;
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.left h2 {
  font-size: 60px;
  font-weight: 400;
  line-height: 0.8;
  font-family: "Canva Sans";
}

.left h1 {
  font-size: 74px;
  font-weight: 900;
  margin: 10px 0;
  line-height: 1.0;
  font-family: "Racing Sans One";
}

.left p {
  font-size: 22px;
  margin-top: 10px;
}

p.pts {
  margin: 8px;
}

.signup-button {
  margin-top: 30px;
  width: 280px;
  height: 56px;
  border: 2px solid black;
  border-radius: 999px;
  background-color: transparent;
  cursor: pointer;
  font-weight: bold;
  font-size: 18px;
}
.right {
  flex: 1;
  padding: 48px;
  background-color: #00bf63;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  border-radius: 32px;
  max-width: 500px;
}
.right h2 {
  font-size: 36px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 24px;
}
form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 300px;
  margin: 0 auto;
}
label {
  font-size: 18px;
}
input[type="text"],
input[type="password"] {
  height: 48px;
  border: none;
  border-radius: 999px;
  padding: 0 16px;
  background-color: white;
  font-size: 16px;
  width: 100%;
}
.remember {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
}
.checkbox {
  width: 16px;
  height: 16px;
  cursor: pointer;
  margin-left: 20px;
}
.forgot {
  text-align: center;
  font-size: 16px;
  color: black;
  text-decoration: none;
  line-height: 0.2;
  margin-bottom: 20px;
}
.forgot:hover {
  text-decoration: underline;
}
.signin-button {
  margin-top: 10px;
  height: 56px;
  border-radius: 999px;
  background-color: transparent; 
  color: white;                  
  font-weight: bold;
  border: 2px solid white;     
  cursor: pointer;
  width: 300px;
  font-size: 18px;
}
.signin-button:hover {
  background-color: rgba(255, 255, 255, 0.1); 
}
h1.acerojt {
  padding: 0px;
  margin: 0px;
  font-family: "Racing Sans One";
  font-weight: bold;
}
.acerlogs {
  margin-top: 40px;
}
.eye-toggle {
  position: absolute;
  top: 14px;
  right: 16px;
  cursor: pointer;
  color: gray;
  font-size: 18px;
}
</style>

<body>
  <div class="container">
    <div class="left">
      <h2>Welcome to</h2>
      <h1 class="acerojt">AcerOJT</h1>      
      <p class="pts">Proud to serve, proud of Acer</p>
      <img src="images/ojtlogo.png" class="acerlogs" alt="Acer Logo" style="width: 240px; margin-bottom: 20px; border-radius: 10px;" />
      <button class="signup-button transition" data-href="signup.php">Sign up</button>
    </div>

    <div class="right">
      <h2>Sign In</h2>
      <?php if (!empty($error)): ?>
        <div style="background: #fff3f3; color: #b00020; padding: 10px; border-radius: 8px; margin-bottom: 10px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" autocomplete="on">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" autocomplete="username" required />

        <label for="password">Password</label>
        <div style="position: relative;">
          <input type="password" id="password" name="password" autocomplete="current-password"  style="padding-right: 40px;" />
          <span class="eye-toggle" onclick="togglePassword()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
        </svg></span>
        </div>

        <div class="remember">
          <input class="checkbox" type="checkbox" id="remember" name="remember" />
          <label for="remember">Remember me for 30 days</label>
        </div>

        <a href="forgot_password.php" class="forgot">Forgot Password?</a>
        <button type="submit" class="signin-button">Sign in</button>
      </form>
      

    </div>
  </div>

  <script>
  function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
  }

  document.querySelectorAll('a.transition, button.transition').forEach(el => {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      const href = el.getAttribute('href') || el.dataset.href;
      document.body.classList.add('fade-out');
      setTimeout(() => {
        window.location.href = href;
      }, 600);
    });
  });

 document.addEventListener("DOMContentLoaded", () => {
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");

  const rememberedUsername = <?= json_encode($cookie_username) ?>;

  function togglePasswordField() {
  const enteredUsername = usernameInput.value.trim();

  if (enteredUsername && rememberedUsername && enteredUsername === rememberedUsername) {
    passwordInput.disabled = true;
    passwordInput.removeAttribute("required");
    passwordInput.style.backgroundColor = "#dbd8d8ff";
    passwordInput.value = ".............";
  } else {
    passwordInput.disabled = false;
    passwordInput.setAttribute("required", "required");
    passwordInput.style.backgroundColor = "";
    passwordInput.value = ""; 
  }
}


  usernameInput.addEventListener("input", togglePasswordField);
  togglePasswordField();
});
</script>

</body>
</html>
