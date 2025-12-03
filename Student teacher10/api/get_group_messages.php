<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

try {
    // connect to messaging DB
    $db = getMessageDB();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Messaging DB not available', 'error' => $e->getMessage()]);
    exit;
}

// Fetch recent group messages (to_type='group' OR to_id='all')
try {
    $sql = "SELECT m.* FROM messages m
            WHERE (m.to_type = 'group' OR m.to_id = 'all' OR m.to_id = 'teachers' OR m.to_id = 'students')
            AND NOT EXISTS (SELECT 1 FROM message_deletions md WHERE md.message_id = m.message_id AND md.deleted_for_everyone = 1)
            ORDER BY m.created_at ASC
            LIMIT 500";
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'messages' => $rows]);
    exit;
} catch (Exception $e) {
    // Log error for diagnostics
    @file_put_contents(__DIR__ . '/../logs/api_errors.log', '[' . date('c') . '] get_group_messages error: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch messages', 'error' => $e->getMessage()]);
    exit;
}
