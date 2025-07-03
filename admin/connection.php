<?php
/* connection.php — MUST return a $pdo object */
$host     = 'localhost';
$dbname   = 'ojtformv3';   // ⬅ change to your DB name
$username = 'root';           // ⬅ DB user
$password = '';               // ⬅ DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
