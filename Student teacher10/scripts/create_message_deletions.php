<?php
require_once __DIR__ . '/../database/config.php';

try {
    $db = getDB();
    $sql = file_get_contents(__DIR__ . '/../database/init_message_deletions.sql');
    $db->exec($sql);
    echo "message_deletions table created or already exists\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
