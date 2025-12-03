<?php
/**
 * Ensure messaging schema exists: creates `messages` and `message_deletions` tables
 * Run: php .\scripts\ensure_messaging_schema.php
 */
require_once __DIR__ . '/../database/config.php';

try {
    $db = getDB();

    $sql = [];

    $sql[] = "CREATE TABLE IF NOT EXISTS `messages` (
        `message_id` INT AUTO_INCREMENT PRIMARY KEY,
        `from_type` VARCHAR(32) NOT NULL,
        `from_id` VARCHAR(64) NOT NULL,
        `to_type` VARCHAR(32) NOT NULL,
        `to_id` VARCHAR(64) NOT NULL,
        `message` LONGTEXT,
        `attachment` VARCHAR(512) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`to_type`,`to_id`),
        INDEX (`from_type`,`from_id`),
        INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $sql[] = "CREATE TABLE IF NOT EXISTS `message_deletions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `message_id` INT NOT NULL,
        `deleted_by_type` VARCHAR(32) NOT NULL,
        `deleted_by_id` VARCHAR(64) NOT NULL,
        `deleted_for_everyone` TINYINT(1) DEFAULT 0,
        `deleted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`message_id`),
        FOREIGN KEY (`message_id`) REFERENCES `messages`(`message_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // Use message DB connection
    $mdb = getMessageDB();
    foreach ($sql as $s) {
        $mdb->exec($s);
    }

    echo "Messaging schema ensured: tables 'messages' and 'message_deletions' exist.\n";
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, "Error ensuring messaging schema: " . $e->getMessage() . "\n");
    exit(2);
}
