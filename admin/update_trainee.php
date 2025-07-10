<?php
$conn = new mysqli("localhost", "root", "", "ojtformv3");

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
    $department_id = $_POST["department_id"]; // ✅ get from POST

    // ✅ Update trainee with department_id
    $stmt = $conn->prepare("UPDATE trainee t 
        LEFT JOIN users u ON t.user_id = u.user_id 
        SET t.first_name = ?, t.surname = ?, t.school = ?, t.phone_number = ?, t.address = ?, 
            t.schedule_days = ?, t.schedule_start = ?, t.schedule_end = ?, t.department_id = ?, u.email = ?
        WHERE t.trainee_id = ?");
        
    $stmt->bind_param("sssssssssss", $first_name, $surname, $school, $phone, $address,
                      $schedule_days, $schedule_start, $schedule_end, $department_id, $email, $trainee_id);

    if ($stmt->execute()) {
        header("Location: traineeview.php?id=" . urlencode($trainee_id));
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
}
?>
