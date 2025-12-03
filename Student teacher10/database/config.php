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

// Messaging database (separate DB)
define('MSG_DB_NAME', 'student_teacher_messages');

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
 * Create Messaging Database Connection
 */
function connectMessageDB() {
    // Try to connect to the messaging DB. If it does not exist, attempt to create it
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . MSG_DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If database doesn't exist, try to create it using the schema SQL
        $msg = $e->getMessage();
        if (stripos($msg, 'unknown database') !== false || stripos($msg, '1049') !== false) {
            // Attempt to connect without a database to create the messaging DB
            try {
                $dsnNoDB = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
                $adminOptions = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $admin = new PDO($dsnNoDB, DB_USER, DB_PASS, $adminOptions);

                // Read schema file and execute statements one-by-one
                $schemaPath = __DIR__ . '/messages_schema.sql';
                if (is_readable($schemaPath)) {
                    $sql = file_get_contents($schemaPath);
                    try {
                        // Execute the whole schema SQL (CREATE DATABASE, USE, CREATE TABLE ...)
                        $admin->exec($sql);
                    } catch (PDOException $ex) {
                        // Log the schema execution error but continue to attempt reconnect
                        @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] schema exec error: ' . $ex->getMessage() . PHP_EOL, FILE_APPEND);
                    }
                }

                // Try connecting again
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (PDOException $ex2) {
                // Log and rethrow to be handled by caller
                $logMsg = '[' . date('c') . '] Message DB creation failed: ' . $ex2->getMessage() . PHP_EOL;
                @file_put_contents(__DIR__ . '/../logs/api_errors.log', $logMsg, FILE_APPEND);
                throw $ex2;
            }
        }
        // Not an "unknown database" error — rethrow for callers to handle
        throw $e;
    }
}

/**
 * Ensure messaging DB is available and return connection or throw exception
 */
function ensureMessageDB(){
    return getMessageDB();
}

/**
 * Get messaging DB singleton
 */
$GLOBALS['msg_db'] = null;
function getMessageDB(){
    if ($GLOBALS['msg_db'] === null) {
        $GLOBALS['msg_db'] = connectMessageDB();
    }
    return $GLOBALS['msg_db'];
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
