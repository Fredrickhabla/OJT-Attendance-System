<?php
session_start();
include('../connection.php');
require_once '../logger.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'coordinator') {
    header("Location: /ojtform/indexv2.php");
    exit;
}

$coordinator_id = $_SESSION['user_id'] ?? 'unknown';
$coordinator_name = $_SESSION['username'] ?? 'unknown';

$trainee_id = $_POST['trainee_id'];
$remarks = $conn->real_escape_string($_POST['remarks']);
$dept_id = $_POST['dept_id'];

$oldQuery = $conn->query("SELECT remarks, first_name, surname FROM trainee WHERE trainee_id = '$trainee_id'");
$trainee = $oldQuery->fetch_assoc();

$old_remark = $trainee['remarks'] ?? '';
$trainee_name = ucwords(strtolower($trainee['first_name'] . ' ' . $trainee['surname']));


$conn->query("UPDATE trainee SET remarks = '$remarks' WHERE trainee_id = '$trainee_id'");


logTransaction(
    $conn,
    $coordinator_id,
    $coordinator_name,
    "Admin added remark: $trainee_id",
    $coordinator_name
);


logAudit(
    $conn,
    $coordinator_id,
    "Add Remark: $trainee_id",
    $remarks,
    $old_remark,
    $admin_name
);


header("Location: departmentview.php?dept_id=" . urlencode($dept_id));
exit();
?>
