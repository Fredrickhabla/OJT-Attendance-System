<?php
$conn = new mysqli("localhost", "root", "", "ojtformv3");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$trainee_id = $_POST['trainee_id'];
$remarks = $conn->real_escape_string($_POST['remarks']);

$conn->query("UPDATE trainee SET remarks = '$remarks' WHERE trainee_id = '$trainee_id'");

$dept_id = $_POST['dept_id'];
header("Location: departmentview.php?dept_id=" . $dept_id);
exit();
?>
