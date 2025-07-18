<?php
session_start();
require_once 'logger.php';

$conn = new mysqli("localhost", "root", "", "ojtformv3");
$pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"] ?? 'unknown_user';
$full_name = $_SESSION["full_name"] ?? 'System';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $trainee_id = $_POST["trainee_id"];
    $name_parts = explode(" ", trim($_POST["name"]), 2);
    $first_name = $name_parts[0];
    $surname = $name_parts[1] ?? "";

    $email = $_POST["email"];
    $school = $_POST["school"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $schedule_days = $_POST["schedule_days"];
    $schedule_start = $_POST["schedule_start"];
    $schedule_end = $_POST["schedule_end"];
    $department_id = $_POST["department_id"];


    $stmt_old = $conn->prepare("SELECT t.first_name, t.surname, t.school, t.phone_number, t.address, t.schedule_days, t.schedule_start, t.schedule_end, t.department_id, u.email 
        FROM trainee t 
        LEFT JOIN users u ON t.user_id = u.user_id 
        WHERE t.trainee_id = ?");
    $stmt_old->bind_param("s", $trainee_id);
    $stmt_old->execute();
    $result = $stmt_old->get_result();
    $old_data = $result->fetch_assoc();
    $stmt_old->close();

  
    $stmt = $conn->prepare("UPDATE trainee t 
        LEFT JOIN users u ON t.user_id = u.user_id 
        SET 
            t.first_name = ?, 
            t.surname = ?, 
            t.school = ?, 
            t.phone_number = ?, 
            t.address = ?, 
            t.schedule_days = ?, 
            t.schedule_start = ?, 
            t.schedule_end = ?, 
            t.department_id = ?, 
            u.email = ?
        WHERE t.trainee_id = ?");

 
    $stmt->bind_param(
        "sssssssssss",
        $first_name,
        $surname,
        $school,
        $phone,
        $address,
        $schedule_days,
        $schedule_start,
        $schedule_end,
        $department_id,
        $email,
        $trainee_id
    );

    if ($stmt->execute()) {
       
        logTransaction($pdo, $user_id, $full_name, "Updated Trainee Record", $username);

     
        $new_data = [
            "first_name" => $first_name,
            "surname" => $surname,
            "school" => $school,
            "phone_number" => $phone,
            "address" => $address,
            "schedule_days" => $schedule_days,
            "schedule_start" => $schedule_start,
            "schedule_end" => $schedule_end,
            "department_id" => $department_id,
            "email" => $email
        ];

        $changed = [];
        $original = [];

        foreach ($new_data as $key => $new_val) {
            $old_val = $old_data[$key] ?? null;
            if ($old_val != $new_val) {
                $changed[$key] = $new_val;
                $original[$key] = $old_val;
            }
        }

        if (!empty($changed)) {
            logAudit($pdo, $user_id, "Update Trainee [$trainee_id]", json_encode($changed), json_encode($original), $username);
        }

     
        header("Location: traineeview.php?id=" . urlencode($trainee_id) . "&update=success");
        exit();
    } else {
     
        echo "Error updating trainee: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
