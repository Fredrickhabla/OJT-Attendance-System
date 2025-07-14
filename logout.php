<?php
session_start();
require_once 'logger.php';

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=ojtformv3", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            SELECT u.username, CONCAT(t.first_name, ' ', t.surname) AS full_name
            FROM users u
            LEFT JOIN trainee t ON u.user_id = t.user_id
            WHERE u.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $username = $row['username'] ?? 'UnknownUser';
        $full_name = $row['full_name'] ?? 'Unknown Name';

        logTransaction($pdo, $user_id, $full_name, "Logged out", $username);
    } catch (PDOException $e) {
        // Optionally log this error to a file
    }
}

// 🔒 Just destroy session (don't remove rememberme)
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
