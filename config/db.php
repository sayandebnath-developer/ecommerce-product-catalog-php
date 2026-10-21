<?php
// config/db.php - database connection (MySQLi)

$host   = 'localhost';
$user   = 'root';   // XAMPP default
$pass   = '';       // XAMPP default (empty)
$dbname = 'ecommerce_catalog';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $dbname);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Database connection failed. Please check your settings.');
}