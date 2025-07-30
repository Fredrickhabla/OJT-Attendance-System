<?php
include('../connection.php');

$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

$whereClause = "WHERE t.active = 'Y'";

if (!empty($search)) {
    $whereClause .= " AND (
        CONCAT(t.first_name, ' ', t.surname) LIKE '%$search%' OR
        u.email LIKE '%$search%' OR
        t.phone_number LIKE '%$search%' OR
        t.address LIKE '%$search%'
    )";
}

$sql = "SELECT t.*, u.email 
        FROM trainee t
        LEFT JOIN users u ON t.user_id = u.user_id
        $whereClause
        ORDER BY t.surname ASC
        LIMIT 50";

$result = $conn->query($sql);

$trainees = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullName = ucwords(strtolower($row["first_name"] . ' ' . $row["surname"]));
        $fullAddress = $row["address"];
        if (preg_match('/(?:\b|^)([\w\s]+),?\s+([\w\s]+)$/', $fullAddress, $matches)) {
            $district = ucwords(strtolower(trim($matches[1])));
            $city = ucwords(strtolower(trim($matches[2])));
            $shortAddress = "$district, $city";
        } else {
            $shortAddress = ucwords(strtolower($fullAddress));
        }

        $trainees[] = [
            "trainee_id" => $row["trainee_id"],
            "name" => $fullName,
            "email" => $row["email"],
            "phone" => $row["phone_number"],
            "address" => $shortAddress,
            "image" => !empty($row["profile_picture"]) ? "/ojtform/" . $row["profile_picture"] : "/ojtform/images/placeholder.jpg"
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($trainees);
?>
