<?php
/**
 * Database Connection
 * Uses environment variables for secure configuration
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Common PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // In development, auto-create database
    if (APP_ENV === 'development') {
        // Step 1: Connect to MySQL server (no DB selected)
        $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Step 2: Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    }

    // Step 3: Connect to database
    $dsnDb = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsnDb, DB_USER, DB_PASS, $options);

    // Step 4: Create users table (only in development or if it doesn't exist)
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
    // Only run this check in development mode
    if (APP_ENV === 'development') {
        try {
            $sth = $pdo->prepare("SELECT DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'birthday'");
            $sth->execute([DB_NAME]);
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
                        trigger_error("Could not alter users.birthday to DATE: " . $inner->getMessage(), E_USER_WARNING);
                    }
                }
            }
        } catch (PDOException $e) {
            // ignore informational check errors
        }
    }

} catch (PDOException $e) {
    // In production, log this instead of displaying
    if (APP_ENV === 'production') {
        error_log("DB setup failed: " . $e->getMessage());
        die("Database connection failed. Please contact the administrator.");
    } else {
        die("DB setup failed: " . $e->getMessage());
    }
}

// $pdo is now available for all other files that include db.php