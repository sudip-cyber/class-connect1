<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['key'])) {
    echo json_encode(['ok' => false, 'error' => 'missing payload']);
    exit;
}
$key = $data['key'];
$value = isset($data['value']) ? $data['value'] : null;

// Store raw string values as-is; non-string values will be JSON-encoded
$jsonVal = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);

// Insert or update
$sql = 'INSERT INTO kv_store (kv_key, kv_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE kv_value = VALUES(kv_value)';
$res = db_query($sql, 'ss', [$key, $jsonVal]);
if ($res === false) {
    echo json_encode(['ok' => false, 'error' => 'db_error']);
} else {
    echo json_encode(['ok' => true]);
}
?>
