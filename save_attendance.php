<?php
session_start();

// ✅ MySQL database connection
$host = "localhost";
$dbname = "ojtform";   // change if needed
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

// ✅ Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $user_id        = $_SESSION['user_id'];
    $date           = $_POST['date'] ?? '';
    $morning_in     = $_POST['morning_in'] ?? '';
    $morning_out    = $_POST['morning_out'] ?? '';
    $afternoon_in   = $_POST['afternoon_in'] ?? '';
    $afternoon_out  = $_POST['afternoon_out'] ?? '';
    $hours          = floatval($_POST['hours'] ?? 0);
    $work_desc      = $_POST['work_description'] ?? '';

    // Validate required fields
    if (empty($date) || empty($morning_in) || empty($morning_out) || empty($afternoon_in) || empty($afternoon_out) || empty($hours) || empty($work_desc)) {
        die("All fields are required.");
    }

    // handle signature upload with Remove.bg background removal
    $signature_path = '';
    if (!empty($_FILES['signature']['name'])) {
        if ($_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['signature']['tmp_name'];
            $apiKey = 'YOUR_REMOVE_BG_API_KEY'; // 
            // Call Remove.bg API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.remove.bg/v1.0/removebg');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "X-Api-Key: $apiKey"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'image_file' => new CURLFile($tmpName),
                'size' => 'auto',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uniqueFilename = uniqid("sig_") . ".png";
                $destination = $uploadDir . $uniqueFilename;

                file_put_contents($destination, $response);
                $signature_path = $destination;
            } else {
                die("Failed to remove background: $response");
            }
        } else {
            die("Signature upload error.");
        }
    }

    // ✅ Insert data into the database
    $stmt = $conn->prepare(
        "INSERT INTO attendance_records 
        (user_id, date, morning_in, morning_out, afternoon_in, afternoon_out, hours, work_description, signature, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssdss",
        $user_id,
        $date,
        $morning_in,
        $morning_out,
        $afternoon_in,
        $afternoon_out,
        $hours,
        $work_desc,
        $signature_path
    );

    if ($stmt->execute()) {
        // Success
        header("Location: success.php");
        exit();
    } else {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
