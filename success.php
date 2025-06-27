<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: indexv2.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ojtform");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$record = null;

// Fetch latest attendance record
$stmt = $conn->prepare("SELECT * FROM attendance_records WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $record = $result->fetch_assoc();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Submitted</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background: linear-gradient(135deg, #00bf63, #009f9d);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      color: #333;
    }
    .container {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      max-width: 700px;
      width: 100%;
      padding: 40px;
      animation: fadeIn 0.7s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      margin-bottom: 20px;
    }
    .header img {
      width: 50px;
      height: 50px;
      object-fit: contain;
      border-radius: 8px;
    }
    h1 {
      font-size: 32px;
      color: #00bf63;
    }
    p {
      font-size: 18px;
      color: #555;
      margin-bottom: 20px;
      text-align: center;
    }
    .details {
      background: #f8f8f8;
      padding: 20px;
      border-radius: 16px;
      margin-bottom: 20px;
    }
    .details label {
      font-weight: bold;
      color: #333;
      display: block;
      margin-top: 10px;
    }
    .details .value {
      margin-bottom: 10px;
      color: #555;
    }
    .btn {
      display: inline-block;
      padding: 14px 30px;
      background: #00bf63;
      color: white;
      text-decoration: none;
      font-weight: bold;
      border-radius: 999px;
      font-size: 18px;
      transition: background 0.3s;
      text-align: center;
    }
    .btn:hover {
      background: #008f4f;
    }
    .logout {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #00bf63;
      font-weight: bold;
      transition: color 0.3s;
      text-align: center;
    }
    .logout:hover {
      color: #008f4f;
    }
    img.signature {
      width: 200px;
      margin-top: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="images/ojtlogo.png" alt="Logo" />
      <h1>Success!</h1>
    </div>
    <p>Thank you, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>. Your attendance has been recorded.</p>

    <?php if ($record): ?>
    <div class="details">
      <label>Date:</label>
      <div class="value"><?= htmlspecialchars($record['date']) ?></div>

      <label>Time In:</label>
      <div class="value"><?= date("g:i A", strtotime($record['time_in'])) ?></div>

      <label>Time Out:</label>
      <div class="value"><?= date("g:i A", strtotime($record['time_out'])) ?></div>

      <label>No. of Hours:</label>
      <div class="value"><?= htmlspecialchars($record['hours']) ?></div>

      <label>Work Description:</label>
      <div class="value"><?= nl2br(htmlspecialchars($record['work_description'])) ?></div>

      <label>E-Signature Image:</label><br>
      <?php if (!empty($record['signature']) && file_exists($record['signature'])): ?>
        <img src="<?= htmlspecialchars($record['signature']) ?>" alt="Signature" class="signature">
      <?php else: ?>
        <div class="value">No signature uploaded.</div>
      <?php endif; ?>
    </div>
    <?php else: ?>
      <p>No attendance record found.</p>
    <?php endif; ?>

    <div style="text-align: center;">
      <a href="attendance_form.php" class="btn">Submit Another Attendance</a><br>
      <a class="logout" href="logout.php">Log out</a>
    </div>
  </div>
</body>
</html>
