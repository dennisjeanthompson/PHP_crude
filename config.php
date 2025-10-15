<?php
/**
 * Configuration loader
 * Loads configuration from environment variables or .env file
 */

// Function to load .env file if it exists
function loadEnvFile($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Only set if not already set in environment
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load .env file if it exists
loadEnvFile(__DIR__ . '/.env');

// Get configuration with fallback to defaults
function getConfig($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

// Define configuration constants
define('APP_ENV', getConfig('APP_ENV', 'production'));
define('DB_HOST', getConfig('DB_HOST', 'localhost'));
define('DB_NAME', getConfig('DB_NAME', 'cit173n_dst_validation'));
define('DB_USER', getConfig('DB_USER', 'student'));
define('DB_PASS', getConfig('DB_PASS', '123qwe'));
define('DISPLAY_ERRORS', getConfig('DISPLAY_ERRORS', '0'));

// Configure error reporting based on environment
if (APP_ENV === 'development' || DISPLAY_ERRORS === '1') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    // In production, errors should be logged, not displayed
    ini_set('log_errors', 1);
}
