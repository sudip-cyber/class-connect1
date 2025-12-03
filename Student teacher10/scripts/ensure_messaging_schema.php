<?php
/**
 * Ensure messaging schema is ready for group messages.
 *
 * Usage: php scripts\ensure_messaging_schema.php
 */
require_once __DIR__ . '/../database/config.php';

try {
    $db = getDB();
    // Determine current column type for to_type
    $stmt = $db->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'messages' AND COLUMN_NAME = 'to_type'");
    $stmt->execute([DB_NAME]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $altered = false;
    if ($row) {
        $colType = strtolower($row['COLUMN_TYPE'] ?? '');
        echo "messages.to_type current COLUMN_TYPE: " . $colType . "\n";
        if (strpos($colType, 'enum(') === 0) {
            echo "Altering messages.from_type and messages.to_type to VARCHAR(32)...\n";
            $db->exec("ALTER TABLE messages MODIFY from_type VARCHAR(32) NOT NULL;");
            $db->exec("ALTER TABLE messages MODIFY to_type VARCHAR(32) NOT NULL;");
            echo "Alter complete.\n";
            $altered = true;
        } else {
            echo "No ALTER needed for messages.to_type.\n";
        }
    } else {
        echo "Could not read messages.to_type column info. Is the 'messages' table present?\n";
    }

    // Ensure message_deletions table exists
    $db->exec("CREATE TABLE IF NOT EXISTS message_deletions (
        message_id INT UNSIGNED NOT NULL,
        deleted_by_type VARCHAR(32) NOT NULL,
        deleted_by_id VARCHAR(128) NOT NULL,
        deleted_for_everyone TINYINT(1) NOT NULL DEFAULT 0,
        deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (message_id, deleted_by_type, deleted_by_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Ensured message_deletions table exists.\n";

    if ($altered) {
        echo "Note: You should re-send a canonical group message to populate group stream rows, or re-run any broadcast flows.\n";
    }

    echo "Done.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
