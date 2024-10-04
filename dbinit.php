<?php
// dbinit.php

// Database credentials - Ideally, use environment variables or a separate configuration file
$servername = getenv('DB_SERVER') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';     
$dbname = getenv('DB_NAME') ?: 'shoe_db';

// Create a new MySQLi instance
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    exit('Database connection error.');
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    error_log("Error creating database: " . $conn->error);
    exit('Database setup error.');
}

// Select the database
if (!$conn->select_db($dbname)) {
    error_log("Database selection failed: " . $conn->error);
    exit('Database selection error.');
}

// Create 'shoes' table if it doesn't exist
$shoesTableSQL = "CREATE TABLE IF NOT EXISTS `shoes` (
    `ShoeID` INT AUTO_INCREMENT PRIMARY KEY,
    `ShoeName` VARCHAR(255) NOT NULL,
    `ShoeDescription` TEXT NOT NULL,
    `QuantityAvailable` INT NOT NULL,
    `Price` DECIMAL(10,2) NOT NULL,
    `ProductAddedBy` VARCHAR(50) NOT NULL DEFAULT 'Ashok',
    `Size` VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($shoesTableSQL) === FALSE) {
    error_log("Error creating shoes table: " . $conn->error);
    exit('Shoes table setup error.');
}

// Function to sanitize inputs
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
