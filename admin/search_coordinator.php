<?php
include('../connection.php');

$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

$where = "WHERE active = 'Y'";
if (!empty($search)) {
    $where .= " AND (
        name LIKE '%$search%' OR
        position LIKE '%$search%' OR
        email LIKE '%$search%' OR
        phone LIKE '%$search%' OR
        school LIKE '%$search%'
    )";
}

$sql = "SELECT * FROM coordinator $where ORDER BY name ASC LIMIT 50";
$result = $conn->query($sql);

$coordinators = [];

if ($result && $result->num_rows > 0) {
    while ($coor = $result->fetch_assoc()) {
        $coordinator_id = $coor['coordinator_id'];

        $traineeQuery = "SELECT CONCAT(first_name, ' ', surname) AS name FROM trainee WHERE coordinator_id = '$coordinator_id'";
        $traineeResult = $conn->query($traineeQuery);

        $trainees = [];
        while ($trainee = $traineeResult->fetch_assoc()) {
            $trainees[] = ucwords(strtolower($trainee['name']));
        }

        $coordinators[] = [
            "id" => $coordinator_id,
            "name" => $coor['name'],
            "position" => $coor['position'],
            "email" => $coor['email'],
            "phone" => $coor['phone'],
            "address" => $coor['school'] ?? 'N/A',
            "image" => !empty($coor['profile_picture']) ? "/ojtform/" . $coor['profile_picture'] : "/ojtform/images/placeholdersquare.jpg",
            "trainees" => $trainees
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($coordinators);
?>