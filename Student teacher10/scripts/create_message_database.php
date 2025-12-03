<?php
/**
 * Create the messaging database if it doesn't exist.
 * Run: php .\scripts\create_message_database.php
 */
require_once __DIR__ . '/../database/config.php';

try {
    // connect to MySQL without specifying DB
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    $dbName = MSG_DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

    echo "Database '$dbName' ensured.\n";
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, "Error creating message database: " . $e->getMessage() . "\n");
    exit(2);
}
