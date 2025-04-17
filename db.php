<?php
// db.php - Global database connection file
try {
    $host = 'localhost'; // XAMPP default host
    $dbname = 'inventory_system'; // Database name
    $username = 'root'; // XAMPP default username
    $password = ''; // XAMPP default password (empty)

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>