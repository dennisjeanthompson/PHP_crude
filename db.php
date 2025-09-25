<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    // Step 1: Connect to MySQL server (no DB selected)
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Step 2: Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // Step 3: Connect to database
    $dsnDb = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsnDb, $user, $pass, $options);

    // Step 4: Create users table
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            birthday DATE NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )
    ";
    $pdo->exec($createTableSQL);

    // Ensure the birthday column is actually a DATE (handle older/wrong schemas)
    try {
        $sth = $pdo->prepare("SELECT DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'birthday'");
        $sth->execute([$db]);
        $col = $sth->fetch();
        if ($col) {
            $dataType = strtolower($col['DATA_TYPE'] ?? '');
            if ($dataType !== 'date') {
                // Attempt to alter the column to DATE
                // This may fail if the server user lacks privileges or data cannot be converted
                try {
                    $pdo->exec("ALTER TABLE users MODIFY birthday DATE NOT NULL");
                } catch (PDOException $inner) {
                    // If alter fails, show a helpful message during development
                    // In production you'd log this instead
                    trigger_error("Could not alter users.birthday to DATE: " . $inner->getMessage(), E_USER_WARNING);
                }
            }
        }
    } catch (PDOException $e) {
        // ignore informational check errors
    }

} catch (PDOException $e) {
    // For production, you'd log this instead of displaying
    die("DB setup failed: " . $e->getMessage());
}

// $pdo is now available for all other files that include db.php