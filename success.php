<?php
session_start();


if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

require_once 'connection.php';

$success = "";
$error = "";
$attendance = null;


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $date = $_POST["date"];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $hours = $_POST["hours"];
    $work_description = trim($_POST["work_description"]);


$trainee_stmt = $conn->prepare("SELECT trainee_id FROM trainee WHERE user_id = ?");
$trainee_stmt->bind_param("s", $user_id);
$trainee_stmt->execute();
$trainee_stmt->store_result();

if ($trainee_stmt->num_rows === 1) {
    $trainee_stmt->bind_result($trainee_id);
    $trainee_stmt->fetch();
    $trainee_stmt->close();

   
    $date = $_POST["date"];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $hours = $_POST["hours"];
    $work_description = trim($_POST["work_description"]);

   
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
        $record_id = uniqid();
        $stmt = $conn->prepare("INSERT INTO attendance_record(attendance_id, trainee_id, date, time_in, time_out, hours, work_description, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiss", $record_id, $trainee_id, $date, $time_in, $time_out, $hours, $work_description, $signature_path);

        if ($stmt->execute()) {
            $success = "Attendance submitted successfully.";
            $attendance = [
                "date" => $date,
                "time_in" => $time_in,
                "time_out" => $time_out,
                "hours" => $hours,
                "description" => $work_description,
                "signature_image" => $signature_path
            ];
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }

} else {
    $error = "Trainee record not found for this user.";
    $trainee_stmt->close();
}
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Success</title>
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
      max-width: 550px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      text-align: center;
      position: relative;
    }
    .header img {
      width: 80px;
      margin-bottom: 10px;
    }
    .header h1 {
      color: #00bf63;
      font-size: 28px;
      margin: 10px 0 0 0;
    }
    .header p {
      margin: 4px 0 20px;
      font-size: 16px;
      color: #555;
    }
    .logout-button {
      display: inline-block;
      background: #b30000;
      color: white;
      padding: 10px 20px;
      border-radius: 999px;
      font-weight: bold;
      text-decoration: none;
      position: absolute;
      top: 20px;
      right: 20px;
      transition: background 0.3s;
    }
    .logout-button:hover {
      background: #800000;
    }
    .success-box {
      background: #e6f9ed;
      color: #007a3d;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      margin-bottom: 20px;
    }
    .info-list {
      text-align: left;
      font-size: 16px;
      line-height: 1.8;
    }
    .info-list strong {
      color: #00bf63;
    }
    .signature-img {
      margin-top: 20px;
      max-width: 100%;
      height: auto;
      border-radius: 10px;
      border: 2px solid #00bf63;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="logout.php" class="logout-button">Logout</a>
    <div class="header">
      <img src="images/ojtlogo.png" alt="AcerOJT Logo" />
      <h1>Attendance Submitted</h1>
      <p>Hello, <strong><?= htmlspecialchars($_SESSION["user_id"]) ?></strong></p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="success-box" style="background:#ffe6e6; color:#b30000;"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($attendance): ?>
      <div class="success-box"><?= $success ?></div>

      <div class="info-list">
        <p><strong>Date:</strong> <?= htmlspecialchars($attendance["date"]) ?></p>
        <p><strong>Time In:</strong> <?= htmlspecialchars($attendance["time_in"]) ?></p>
        <p><strong>Time Out:</strong> <?= htmlspecialchars($attendance["time_out"]) ?></p>
        <p><strong>No. of Hours:</strong> <?= htmlspecialchars($attendance["hours"]) ?></p>
        <p><strong>Work Description:</strong><br><?= nl2br(htmlspecialchars($attendance["description"])) ?></p>

        <?php if (!empty($attendance["signature_image"])): ?>
          <p><strong>Signature Image:</strong></p>
          <img class="signature-img" src="<?= htmlspecialchars($attendance["signature_image"]) ?>" alt="Signature Image" />
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
