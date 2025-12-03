<?php
/**
 * Database Configuration File
 * This file contains all database connection settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'SU16@Noone');
define('DB_NAME', 'student_teacher_db');

// Additional Settings
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_COOKIE_NAME', 'st_session');

// Password Settings
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 10]);

/**
 * Create Database Connection
 */
function connectDB() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die('Database Connection Error: ' . $e->getMessage());
        } else {
            die('Database Connection Error. Please contact administrator.');
        }
    }
}

/**
 * Get Database Connection Instance (Singleton)
 */
$GLOBALS['db'] = null;

function getDB() {
    if ($GLOBALS['db'] === null) {
        $GLOBALS['db'] = connectDB();
    }
    return $GLOBALS['db'];
}

// Initialize database connection
$db = getDB();
?>
