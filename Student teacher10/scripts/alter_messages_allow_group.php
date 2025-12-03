<?php
require_once __DIR__ . '/../database/config.php';
try {
    $db = getDB();
    // Change from_type and to_type from ENUM to VARCHAR to allow 'group'
    $db->exec("ALTER TABLE messages MODIFY from_type VARCHAR(32) NOT NULL;");
    $db->exec("ALTER TABLE messages MODIFY to_type VARCHAR(32) NOT NULL;");
    echo "messages table altered: from_type/to_type now VARCHAR(32)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
