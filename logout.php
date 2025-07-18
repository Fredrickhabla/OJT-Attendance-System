<?php
session_start();
require_once 'logger.php';

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $roleStmt = $pdo->prepare("SELECT role, name, username FROM users WHERE user_id = ?");
        $roleStmt->execute([$user_id]);
        $userData = $roleStmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
    $role = $userData['role'] ?? 'unknown';
    $username = $userData['username'] ?? 'UnknownUser';

    if ($role === 'admin') {
    if (!empty($userData['name']) && trim($userData['name']) !== '') {
        $full_name = trim($userData['name']);
    } else {
        $full_name = $username;
    }
}

    elseif ($role === 'student') {
        $stmt = $pdo->prepare("
            SELECT CONCAT(first_name, ' ', surname) AS full_name
            FROM trainee
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $full_name = $row['full_name'] ?? 'Unknown Name';

    } elseif ($role === 'coordinator') {
        $stmt = $pdo->prepare("
            SELECT name AS full_name
            FROM coordinator
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $full_name = $row['full_name'] ?? 'Unknown Name';
    } else {
        $full_name = 'Unknown Role';
    }
} else {
    $role = 'unknown';
    $username = 'UnknownUser';
    $full_name = 'Unknown Name';
}


        logTransaction($pdo, $user_id, $full_name, "Logged out", $username);

    } catch (PDOException $e) {
        
    }
}


$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();


header("Location: indexv2.php");
exit;

?>