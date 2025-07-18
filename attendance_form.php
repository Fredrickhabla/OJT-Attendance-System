<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Optional fallback for full_name
$full_name = isset($_SESSION["full_name"]) ? $_SESSION["full_name"] : "User";

require_once 'connection.php';

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $date = $_POST["date"];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $hours = $_POST["hours"];
    $work_description = trim($_POST["work_description"]);

    // Handle file upload
    $signature_path = "";
    if (!empty($_FILES["signature"]["name"])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = basename($_FILES["signature"]["name"]);
        $target_file = $upload_dir . uniqid() . "_" . $filename;
        if (move_uploaded_file($_FILES["signature"]["tmp_name"], $target_file)) {
            $signature_path = $target_file;
        } else {
            $error = "Failed to upload signature image.";
        }
    }

    if (empty($error)) {
        // Use the correct table name
        $stmt = $conn->prepare("INSERT INTO attendance_records (user_id, date, time_in, time_out, hours, work_description, signature_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiss", $user_id, $date, $time_in, $time_out, $hours, $work_description, $signature_path);

        if ($stmt->execute()) {
            $success = "Attendance submitted successfully.";
        } else {
            $error = "Error saving attendance: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Form</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #00bf63;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 40px 0;
    }

    .container {
      background: white;
      border-radius: 24px;
      padding: 40px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      text-align: center;
    }

    .header img {
      width: 80px;
      margin-bottom: 10px;
    }

    .header h1 {
      color: #00bf63;
      margin: 10px 0 0 0;
      font-size: 28px;
    }

    .header p {
      margin: 4px 0 20px;
      font-size: 16px;
      color: #555;
    }

    form {
      text-align: left;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
      color: #333;
    }

    input[type="date"],
    input[type="time"],
    input[type="number"],
    input[type="file"],
    textarea {
      width: 100%;
      border: 2px solid #00bf63;
      border-radius: 10px;
      padding: 10px;
      font-size: 16px;
      margin-top: 5px;
      transition: border 0.3s;
    }

    input[readonly] {
      background: #f5f5f5;
    }

    input:focus,
    textarea:focus {
      border-color: #00994d;
      outline: none;
    }

    button {
      margin-top: 20px;
      width: 100%;
      background: #00bf63;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 999px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background: #00994d;
    }

    .message {
      margin-bottom: 20px;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
    }

    .message.success {
      background: #e6f9ed;
      color: #007a3d;
    }

    .message.error {
      background: #ffe6e6;
      color: #b30000;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="images/ojtlogo.png" alt="AcerOJT Logo" />
      <h1>Attendance Form</h1>
      <p>Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></p>
    </div>

    <?php if (!empty($success)): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="success.php" enctype="multipart/form-data">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required />

      <label for="time_in">Time In</label>
      <input type="time" id="time_in" name="time_in" required onchange="calculateHours()" />

      <label for="time_out">Time Out</label>
      <input type="time" id="time_out" name="time_out" required onchange="calculateHours()" />

      <label for="hours">No. of Hours</label>
      <input type="number" id="hours" name="hours" readonly />

      <label for="work_description">Work Description</label>
      <textarea id="work_description" name="work_description" rows="3" placeholder="Describe your work today" required></textarea>

      <label for="signature">E-Signature Image</label>
      <input type="file" id="signature" name="signature" accept="image/*" />

      <button type="submit">Submit Attendance</button>
    </form>
  </div>

  <script>
    function calculateHours() {
      const timeIn = document.getElementById('time_in').value;
      const timeOut = document.getElementById('time_out').value;

      if (timeIn && timeOut) {
        const [inHour, inMinute] = timeIn.split(':').map(Number);
        const [outHour, outMinute] = timeOut.split(':').map(Number);

        let inTotal = inHour * 60 + inMinute;
        let outTotal = outHour * 60 + outMinute;

        let diffMinutes = outTotal - inTotal;

        if (diffMinutes < 0) {
          diffMinutes += 24 * 60; // Handle overnight
        }

        const hours = Math.round(diffMinutes / 60);
        document.getElementById('hours').value = hours;
      } else {
        document.getElementById('hours').value = "";
      }
    }
  </script>
</body>
</html>
