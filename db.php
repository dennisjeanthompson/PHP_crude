<?php
// Error display settings for production hosting (InfinityFree compatible)
// Note: InfinityFree requires display_errors to be OFF in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Database Configuration for InfinityFree Hosting
// IMPORTANT: Update these values with your InfinityFree database credentials
// You can find these in your InfinityFree control panel under "MySQL Databases"
// 
// InfinityFree database details:
// - Host: Typically 'sql000.infinityfreeapp.com' (check your control panel)
// - Database: Format is usually 'if0_XXXXXXXX_dbname' (provided by InfinityFree)
// - Username: Format is usually 'if0_XXXXXXXX' (provided by InfinityFree)
// - Password: Your database password (set in control panel)
//
// Example InfinityFree configuration:
// $host = 'sql000.infinityfreeapp.com';
// $db   = 'if0_12345678_mydb';
// $user = 'if0_12345678';
// $pass = 'your_password_here';

$host = 'localhost';
$db   = 'cit173n_dst_validation';
$user = 'student';
$pass = '123qwe';

// Common PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // InfinityFree Note: Database must be created via control panel
    // Connect directly to the database (CREATE DATABASE not supported on InfinityFree)
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Create users table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            birthday DATE NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )
    ";
    $pdo->exec($createTableSQL);

    // InfinityFree Note: INFORMATION_SCHEMA queries and ALTER TABLE operations
    // may have restricted permissions. The table is created with correct DATE type above,
    // so schema validation is skipped for hosting compatibility.

} catch (PDOException $e) {
    // Enhanced error handling for production environment
    // Log error details for debugging (in production, use error_log() instead of die())
    $errorMsg = "Database connection failed. Please check your database credentials and ensure the database exists in your hosting control panel.";
    
    // For development/debugging, you can temporarily enable detailed errors:
    // Uncomment the line below to see detailed error messages during setup
    // $errorMsg .= " Details: " . $e->getMessage();
    
    die($errorMsg);
}

// $pdo is now available for all other files that include db.php