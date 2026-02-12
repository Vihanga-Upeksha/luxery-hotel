<?php
// Start session for admin panel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db = 'hotel_booking';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Connection successful
} catch (PDOException $e) {
    http_response_code(500);
    die("Database Connection Error: " . $e->getMessage());
}
?>
