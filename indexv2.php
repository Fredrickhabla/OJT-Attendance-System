<?php
session_start();

$conn = new mysqli("localhost", "root", "", "ojtform");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if admin special case
    if ($username === "admin" && $password === "admin2314") {
        // Set session variables for admin if needed
        $_SESSION['user_id'] = 0; // example admin id
        $_SESSION['full_name'] = "Admin";
        $_SESSION['ValidAdmin'] = true;  
        header("Location: admin/dashboardv2.php");
        exit();
    }

    // Normal user authentication
    $stmt = $conn->prepare("SELECT id, password, full_name FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hashed_password, $full_name);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['full_name'] = $full_name;
                header("Location: attendance_form.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    } else {
        $error = "Database error.";
    }
}

$conn->close();
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
/* Container */
.container {
  width: 100%;
  max-width: 1280px;
  background-color: white;
  overflow: hidden;
box-shadow: 0 30px 40px 5px rgba(0, 0, 0, 0.4);

  border-top-left-radius: 32px;
  border-bottom-left-radius: 32px;
  border-top-right-radius: 32px;
  border-bottom-right-radius: 32px;

  display: flex;
}

/* Left - Welcome */
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

/* Right - Form */
.right {
  flex: 1;
  padding: 48px;
  background-color: #00bf63;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
border-top-left-radius: 32px;
  border-bottom-left-radius: 32px;
  border-top-right-radius: 32px;
  border-bottom-right-radius: 32px;
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
</style>
<body>
  <div class="container">
    
      <!-- Left - Welcome Section -->
      <div class="left">
        <h2>Welcome to</h2>
        <h1 class ="acerojt">AcerOJT</h1>      
        <p class="pts">Proud to serve, proud of Acer</p>
        <img src="images/ojtlogo.png" class ="acerlogs"alt="Acer Logo" style="width: 240px; margin-bottom: 20px; border-radius: 10px;" />
        <button class="signup-button transition" data-href="signup.php">Sign up</button>
      </div>

      <!-- Right - Sign In Form -->
      <div class="right">
        <h2>Sign In</h2>
       <?php if (!empty($error)): ?>
  <div style="background: #fff3f3; color: #b00020; padding: 10px; border-radius: 8px; margin-bottom: 10px;">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<form method="POST" action="">
  <label for="username">Username</label>
  <input type="text" id="username" name="username" required />

  <label for="password">Password</label>
  <input type="password" id="password" name="password" required />

  <div class="remember">
    <input class="checkbox" type="checkbox" id="remember" />
    <label for="remember">Remember me for 30 days</label>
  </div>

  <a href="#" class="forgot">Forgot Password?</a>
  <button type="submit" class="signin-button">Sign in</button>
</form>
      </div>
    </div>
 

  <script>

    
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


const container = document.querySelector('.container');
document.getElementById('toSignIn').addEventListener('click', () => {
  container.classList.add('right-active');
});
document.getElementById('toSignUp').addEventListener('click', () => {
  container.classList.remove('right-active');
});

document.getElementById('loginForm').addEventListener('submit', e => {
  e.preventDefault();
  alert('Signed In as ' + e.target[0].value);
});


  document.querySelectorAll('button[data-href]').forEach(button => {
    button.addEventListener('click', () => {
      window.location.href = button.getAttribute('data-href');
    });
  });

  </script>
</body>
</html>

