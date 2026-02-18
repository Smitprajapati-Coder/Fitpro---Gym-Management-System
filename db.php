<?php
// db.php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'fitpro';
$DB_USER = 'root';    // default XAMPP MySQL user
$DB_PASS = '';        // default XAMPP MySQL password is empty
$DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $options);
    // echo "Database connected successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
