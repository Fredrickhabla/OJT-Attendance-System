<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: indexv2.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Acer OJT Attendance</title>
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
      margin-bottom: 10px;
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
      margin-bottom: 30px;
      color: #555;
      text-align: center;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    label {
      font-weight: 600;
      font-size: 16px;
    }
    input[type="date"],
    input[type="time"],
    input[type="text"],
    textarea,
    input[type="file"] {
      width: 100%;
      padding: 14px;
      border: 2px solid #ddd;
      border-radius: 12px;
      font-size: 16px;
      transition: border 0.3s;
    }
    input[type="date"]:focus,
    input[type="time"]:focus,
    input[type="text"]:focus,
    textarea:focus {
      border-color: #00bf63;
      outline: none;
    }
    button {
      padding: 14px;
      background: #00bf63;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 999px;
      font-size: 18px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background: #008f4f;
    }
    .logout {
      margin-top: 20px;
      display: inline-block;
      text-decoration: none;
      color: #00bf63;
      font-weight: bold;
      transition: color 0.3s;
      text-align: center;
    }
    .logout:hover {
      color: #008f4f;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="images/ojtlogo.png" alt="Logo" />
      <h1>Attendance Form</h1>
    </div>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong></p>
    <form method="POST" action="save_attendance.php" enctype="multipart/form-data">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" required />

      <label for="time_in">Time In</label>
      <input type="time" id="time_in" name="time_in" required onchange="calculateHours()" />

      <label for="time_out">Time Out</label>
      <input type="time" id="time_out" name="time_out" required onchange="calculateHours()" />

      <label for="hours">No. of Hours</label>
      <input type="text" id="hours" name="hours" />

      <label for="work_desc">Work Description</label>
      <textarea id="work_desc" name="work_desc" rows="3" placeholder="Describe your work today..." required></textarea>

      <label for="signature">E-Signature Image</label>
      <input type="file" id="signature" name="signature" accept="image/*" required />

      <button type="submit">Submit Attendance</button>
    </form>
  </div>

  <script>
    function calculateHours() {
      const timeIn = document.getElementById('time_in').value;
      const timeOut = document.getElementById('time_out').value;
      const hoursField = document.getElementById('hours');

      if (timeIn && timeOut) {
        const [inH, inM] = timeIn.split(':').map(Number);
        const [outH, outM] = timeOut.split(':').map(Number);

        let diffMinutes = (outH * 60 + outM) - (inH * 60 + inM);
        if (diffMinutes < 0) diffMinutes += 24 * 60; // handle overnight

        const hours = Math.floor(diffMinutes / 60);
        const minutes = diffMinutes % 60;

        hoursField.value = `${hours}h ${minutes}m`;
      }
    }
  </script>
</body>
</html>
